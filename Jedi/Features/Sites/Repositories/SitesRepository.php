<?php
namespace Jedi\Features\Sites\Repositories;

use Jedi\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Model;

use Jedi\Repositories\ValidatorInterface;
use Hash, DB;

use Jedi\Features\Sites\Models\InputTemplatesModel;
use Jedi\Features\Sites\Models\UserSitesModel;
use Jedi\Features\Sites\Models\UserPermissionsModel;


class SitesRepository extends EloquentRepository implements SitesInterface
{
    protected $validator;

    protected $rules = [
        'site_name' => 'required'
    ];

    protected $messages = [
        'required' => 'Please enter your :attribute',
    ];


    public function __construct(Model $model, ValidatorInterface $validator)
    {
        parent::__construct($model);

        $this->validator = $validator;
    }

    public function get_all_active_sites()
    {
        return DB::table($this->model->getTable().' as s')
            ->select(DB::raw('s.id as site_id, s.site_name'))
            ->where(['s.is_active' => 1])
            ->join('input_templates as it',  'it.site_id', '=', 's.id')
            ->orderBy('s.site_name', 'asc');
    }

    public function get_site_details($site_id)
    {
        return DB::table('sites as s')
            ->select(DB::raw('s.id as site_id, s.site_name, s.description, s.created_at'))
            ->where('s.id', $site_id)
            ->first();
    }

    public function save_templates(array $data)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {

            DB::beginTransaction();

            $validated = $this->validate_inputs($data);

            if ($validated['status']) {

                $this->model->site_name   = $data['site_name'];
                $this->model->description = $data['site_notes'];
                $this->model->is_active   = 1;

                if ($this->model->save()) {
                    // save input template
                    $template_obj = new InputTemplatesModel();
                    $template_obj->site_id   = $this->model->id;
                    $template_obj->filename  = $data['filename'];
                    $template_obj->location  = $data['location'];
                    $template_obj->is_active = 1;

                    $template_obj->save();
                    DB::commit();

                    $response = ['status' => true, 'message' => 'Input Template saved'];
                }
            } else {
                $response = $validated;
            }

        } catch (\Exception $e) {
            $response['message'] = $this->parse_sql_error_message($e->getMessage());
            DB::rollback();
        }

        return $response;
    }

    public function update_template($site_id, array $attributes)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $template_obj = DB::table('input_templates as it')
            ->where(['it.site_id' => $site_id, 'it.is_active' => 1]);

        if ($template_obj->count()) {

            if ($template_obj->update($attributes)) {
                $response = ['status' => true];
            }

        }

        return $response;
    }

    public function set_template_data($site_id, array $data)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {

            if ($this->update($site_id, $data)) {
                $response = ['status' => true, 'message' => 'Site information has been updated'];
            }

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return$response;
    }

    public function validate_inputs (array $inputs, $rules = null, $messages = null)
    {
        $rules    = is_null($rules) ? $this->rules : $rules;
        $messages = is_null($messages) ? $this->messages : $messages;

        $this->validator->with($inputs);
        $this->validator->rules($rules);
        $this->validator->messages($messages);

        if ($this->validator->passes()) {
            $status  = true;
            $errors  = null;
        } else {
            $status = false;
            $errors = $this->validator->errors();
        }

        return ['status' => $status , 'message' => $errors];
    }

    public function store_user_sites($attributes)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];
        try {
            $userSites = new UserSitesModel();
            $userSites->user_id         = $attributes['user_id'];
            $userSites->site_id         = $attributes['sites'];
            if ($userSites->save()) {
                for($i = 0; $i < count($attributes['permission']); $i++){
                    $userPermissions = new UserPermissionsModel();
                    $userPermissions->user_site_id     = $userSites->id;
                    $userPermissions->permission_id         = $attributes['permission'][$i];
                    $userPermissions->save();
                }
            }
            DB::commit();
            $this->log([
                [
                    'action' => SYSTEM_LOGS_ACTION_INSERT,
                    'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['user_sites' => ['id' => $userSites->id]]),
                    'action_by' => $attributes['action_by']
                ]
            ]);
            $response = ['status' => true, 'message' => 'Site has been saved.'];
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            DB::rollback();
            $this->log([
                [
                    'action' => SYSTEM_LOGS_ACTION_ERROR,
                    'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['error_msg' => $e->getMessage()])
                ]
            ]);
        }
        return $response;
    }

    public function update_user_sites($attributes)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];
        try {
            $this->class = new UserSitesModel();
            $update = $this->class->where("id" , "=" , $attributes['user_site_id'])->first();

            //delete first user_sites
            if(!empty($attributes['permission'])){
                $this->class = new UserPermissionsModel();
                $remove = $this->class->where('user_site_id' , '=' , $attributes['user_site_id']);
                $remove->delete();
            }

            //then save it again
            if ($update->save()) {
                if(isset($attributes['permission'])){
                    for($i = 0; $i < count($attributes['permission']); $i++){
                        $userPermissions = new UserPermissionsModel();
                        $userPermissions->user_site_id  = $attributes['user_site_id'];
                        $userPermissions->permission_id = $attributes['permission'][$i];
                        $userPermissions->save();
                    }
                }
                $response['status']   = true;
                $response['message']  = 'Site has been updated.';
            } else {
                $response['status']   = false;
                $response['message']  = '';
            }
            $this->log([
                [
                    'action' => SYSTEM_LOGS_ACTION_UPDATE,
                    'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['user_sites' => ['id' => $attributes['user_site_id']]]),
                    'action_by' => $attributes['action_by']
                ]
            ]);
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            DB::rollback();
            $this->log([
                [
                    'action' => SYSTEM_LOGS_ACTION_ERROR,
                    'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['error_msg' => $e->getMessage()])
                ]
            ]);
        }
        return $response;
    }

    public function get_permissions()
    {
        try {
            $raw = DB::table('permissions as p');
            $raw->select(DB::raw('p.*'));
            $raw->orderBy('p.permission_name','ASC');
            return $raw->get();
        }catch(Exceptions $e) {
            return $e->getMessage();
        }
    }

    public function get_sites()
    {
        try {
            $raw = DB::table('sites as s');
            $raw->select(DB::raw('s.*'));
            $raw->orderBy('s.site_name','ASC');
            $raw->where('s.is_active','1');
            return $raw->get();
        }catch(Exceptions $e) {
            return $e->getMessage();
        }
    }

    public function get_user_sites($user_id)
    {
        try {
            $raw = DB::table('user_sites as us');
            $raw->select(DB::raw('us.*, us.id as user_site_id,s.*'));
            $raw->join('sites as s','s.id', '=', 'us.site_id');
            $raw->where('us.is_active','1');
            $raw->where('us.user_id','=',$user_id);
            return $raw->get();
        }catch(Exceptions $e) {
            return $e->getMessage();
        }
    }

    public function get_user_permissions()
    {
        try {
            $raw = DB::table('user_permissions as up');
            $raw->select(DB::raw('up.*,p.*'));
            $raw->join('permissions as p','p.id', '=', 'up.permission_id');
            $raw->orderBy('p.permission_name','ASC');
            return $raw->get();
        }catch(Exceptions $e) {
            return $e->getMessage();
        }
    }

    public function get_user_permission_per_id($user_site_id){
        try {
            $raw = DB::table('user_permissions as up');
            $raw->select(DB::raw('up.*,p.*'));
            $raw->join('permissions as p','p.id', '=', 'up.permission_id');
            $raw->orderBy('p.permission_name','ASC');
            $raw->where('up.user_site_id','=',$user_site_id);
            return $raw->get();
        }catch(Exceptions $e) {
            return $e->getMessage();
        }
    }

    public function get_site($id)
    {
        try {
            $raw = DB::table('user_sites as us');
            $raw->select(DB::raw('us.*, us.id as user_site_id,s.*'));
            $raw->join('sites as s','s.id', '=', 'us.site_id');
            $raw->where('us.is_active','1');
            $raw->where('us.id','=',$id);
            return $raw->first();
        }catch(Exceptions $e) {
            return $e->getMessage();
        }
    }

    public function delete_user_site($attributes)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];
        try {
            if(!empty($attributes['id'])){
                $sites = new UserSitesModel();
                $removeSites = $sites->where('id' , '=' , $attributes['id']);
                $removeSites->delete();

                $permissions = new UserPermissionsModel();
                $removePermission = $permissions->where('user_site_id' , '=' , $attributes['id']);
                $removePermission->delete();

                $response['status'] = true;
                $response['message'] = 'Site has been deleted.';

                $this->log([
                    [
                        'action' => SYSTEM_LOGS_ACTION_DELETE,
                        'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                        'new_value' => json_encode(['user_sites' => ['id' => $attributes['id']]]),
                        'action_by' => $attributes['action_by']
                    ]
                ]);
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            DB::rollback();
            $this->log([
                [
                    'action' => SYSTEM_LOGS_ACTION_ERROR,
                    'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['error_msg' => $e->getMessage()])
                ]
            ]);
        }
        return $response;
    }
}