<?php
namespace Jedi\Features\Sites\Controllers;

use Jedi\Controllers\BaseController;
use Jedi\Features\Sites\Models\SitesModel;
use Jedi\Features\Sites\Repositories\SitesInterface;
use Jedi\Features\Sites\Repositories\InputTemplatesInterface;
use Jedi\Features\Documents\Repositories\UploadsInterface;

use Redirect, Request, Input, Response;
use AWS, DB;


class SitesController extends BaseController
{
    protected $siteRepo;
    protected $inputRepo;
    protected $ploadRepo;


    public function __construct (SitesInterface $sitesRepo, InputTemplatesInterface $inputRepo, UploadsInterface $uploadRepo)
    {
        $this->sitesRepo  = $sitesRepo;
        $this->inputRepo  = $inputRepo;
        $this->uploadRepo = $uploadRepo;

        parent::__construct();
    }

    public function index()
    {
        $this->views['title'] = 'Input Templates List';
        $this->views['sites'] = $this->sitesRepo->get_all_active_sites();

        return view('sites::index')->with($this->views);
    }

    public function site_add()
    {
        $this->views['title'] = 'Input Template';

        return view('sites::upload')->with($this->views);
    }

    public function site_save()
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {
            $input = Input::get('sites');

            if (Input::hasFile('site_deffile')) {
                $file  = Input::file('site_deffile');

                $data['site_name']         = $input['site_name'];
                $data['site_notes']        = $input['site_notes'];
                $data['site_deffile']      = $file;
                $data['logged_in_user_id'] = $this->get_auth_user_id();

                // upload XML file in S3
                $validated = $this->uploadRepo->validate_inputs(['file' => $data['site_deffile']]);

                $attributes = [
                    'filename'           => $file->getClientOriginalName(),
                    'original_filename'  => $file->getClientOriginalName(),
                    'extension'          => $file->getMimeType(),
                    'mime_type'          => $file->getMimeType()
                ];

                if ($validated['status']) {
                    $response = $this->uploadRepo->upload(AWS::get('s3'), $data['site_deffile'], $attributes);

                    if ($response['status']) {
                        $data['filename'] = $response['data']['original_filename'];
                        $data['location'] = $response['data']['key'];

                        $response = $this->sitesRepo->save_templates($data);
                    }
                }
             } else {
                $response['message'] = 'Missing upload file';
            }

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            $response['message'] = $e->getMessage();
        }

        return Response::json($response);
    }

    public function site_edit($site_id)
    {
        if (Request::ajax()) {

            if (Request::method('post')) {
                $input = Input::get('sites');
                $data  = ['site_name' => $input['site_name'], 'description' => $input['site_notes']];

                try {
                    $validated = $this->sitesRepo->validate_inputs($data);

                    $response = ['status' => false, 'message' => 'Invalid request'];

                    if ($validated['status']) {
                        $response = $this->sitesRepo->update($site_id, $data);

                        if ($response) {
                            if (Input::hasFile('site_deffile')) {
                                $file = Input::file('site_deffile');

                                $attributes = [
                                    'filename'           => $file->getClientOriginalName(),
                                    'original_filename'  => $file->getClientOriginalName(),
                                    'extension'          => $file->getMimeType(),
                                    'mime_type'          => $file->getMimeType()
                                ];

                                $upload_response = $this->uploadRepo->upload(AWS::get('s3'), $file, $attributes);

                                if ($upload_response['status']) {
                                    $template_data['filename'] = $upload_response['data']['original_filename'];
                                    $template_data['location'] = $upload_response['data']['key'];

                                    $this->sitesRepo->update_template($site_id, $template_data);
                                }
                            }

                            $response = ['status' => true, 'message' => 'Input Template updated'];
                        }

                    } else {
                        $response = $validated;
                    }


                } catch (\Exception $e) {
                    $response = ['status' => false, 'message' => $e->getMessage()];
                }

                return Response::json($response);
            }

        } else {
            $this->views['title'] = 'Edit Input Template';
            $this->views['site']  = $this->sitesRepo->get_site_details($site_id);

            return view('sites::edit')->with($this->views);
        }

    }

    public function site_delete($site_id)
    {
        $response = ['status' => true, 'message' => 'Invalid request'];

        if (Request::method('POST')) {
            $response = $this->sitesRepo->set_template_data($site_id, ['is_active' => 0]);

            if ($response['status']) {
                $sites = $this->sitesRepo->get_all_active_sites();

                $response['html']    = view('sites::partials.lists')->with(['sites' => $sites])->render();
                $response['message'] = 'Input Template has been deleted';
            }
        }

        return Response::json($response);
    }

    public function read_template($site_id)
    {
        $response = $this->inputRepo->read_input_template($site_id);
        return Response::json($response);
    }


    public function save_user_sites()
    {
        if (Request::ajax()) {
            if(Request::isMethod('POST')) {
                $input = Request::all();
                $input['action_by'] = $this->get_auth_user_id();
                $response = $this->sitesRepo->store_user_sites($input);
            }else if(Request::isMethod('PUT')){
                $input = Request::all();
                $input['action_by'] = $this->get_auth_user_id();
                $response = $this->sitesRepo->update_user_sites($input);
            }
            return Response::json($response);
        }
    }

    public function load_user_add_site()
    {
        if (Request::ajax()) {
            if(Request::isMethod('GET')) {
                $user_id = Input::get('user_id');
                $this->views['user_id'] = $user_id;
                $this->views['permissions'] = $this->sitesRepo->get_permissions();
                $this->views['sites'] = $this->sitesRepo->get_sites();
                //user sites
                $this->views['user_sites'] = $this->sitesRepo->get_user_sites($user_id);
                $this->views['user_permissions'] = $this->sitesRepo->get_user_permissions();
                //end user sites
                $response = view('sites::partials.add-site')->with($this->views)->render();
            }
            return Response::json($response);
        }
    }

    public function load_user_site($site_id)
    {
        if (Request::ajax()) {
            if(Request::isMethod('GET')) {
                $user_id = Input::get('user_id');
                $this->views['user_site_id'] = $site_id;
                $this->views['permissions'] = $this->sitesRepo->get_permissions();
                $this->views['sites'] = $this->sitesRepo->get_sites();
                $this->views['user_sites'] = $this->sitesRepo->get_user_sites($user_id);
                $this->views['user_permissions'] = $this->sitesRepo->get_user_permissions();
                $this->views['get_user_permission_per_id'] = $this->sitesRepo->get_user_permission_per_id($site_id);
                $this->views['get_site'] = $this->sitesRepo->get_site($site_id);
                $response = view('sites::partials.edit-site')->with($this->views)->render();
            }
            return Response::json($response);
        }
    }

    public function delete_user_site()
    {
        if (Request::ajax()) {
            if(Request::isMethod('POST')) {
                $input = Request::all();
                $input['action_by'] = $this->get_auth_user_id();
                $response = $this->sitesRepo->delete_user_site($input);
            }
        }
        return Response::json($response);
    }
}