<?php namespace Jedi\Features\Maps\Controllers;

use Jedi\Controllers\BaseController;
use Jedi\Features\Maps\Repositories\MapsInterface;
use Jedi\Features\Documents\Repositories\UploadsInterface;
use Jedi\Features\Maps\Models\MapsModel;

use Input, Request, Response, Storage, DateTime, DB, File,ZipArchive;
use AWS;


class MapsController extends BaseController
{
    protected $mapsRepo;
    protected $uploadsRepo;


    public function __construct(MapsInterface $mapsRepo, UploadsInterface $uploadsRepo)
    {
        $this->mapsRepo = $mapsRepo;
        $this->uploadsRepo = $uploadsRepo;
        $this->s3 = AWS::get('s3');

        parent::__construct();
    }


    public function index()
    {
        // echo url('ExcelFiles/excel.xlsx'); exit;
    $file_name = DB::table('mappings')->orderBy('updated_at', 'desc');
    $query = DB::table('mappings')->select('company', 'file_name')->where('status', '!=', 'mapped')->first();
    $fieldnames = DB::table('fieldnames')->where('status','=','0')->orderBy('fieldname', 'asc')->get();
    $selected_fieldnames = DB::table('fieldnames')->where('status', '=', '1')->orderBy('updated_at', 'asc')->get();


    if ($query) {
        $company = $query->company;
        $name = $query->file_name;
        $extension = pathinfo($name, PATHINFO_EXTENSION);

        $file_xml = '/var/www/jedi/public/XMLFiles/'.$company.".xml";
         
        if (!File::exists($file_xml)) {
            // dd('hello');
            // DB::table('mappings')->where('company', '=', $company)->delete(); //delete record from db

            // $file_pdf = '/var/www/jedi/storage/app/'.$company."." .$extension;
            // $file_txt = '/var/www/jedi/storage/app/TextFiles/'.$company.".txt";

            // File::delete($file_pdf); // delete pdf
            // File::delete($file_txt); //delete txt
            
        }

    }   //automatic delete if mapping status == ''
        return view('Maps::home.index')->with(['lists' => $file_name->get(), 'results' => $fieldnames, 'selected' => $selected_fieldnames])->render();              
    }

   
   public function upload() 
   {
        $response = ['status' => false, 'message' => 'Invalid request'];

        if (Request::ajax()) {

            if (Request::isMethod('POST')) {

                if (Input::hasFile('file') && Input::get('company')!=null) {
                    
                    $file = Input::file('file');
                    $comp = Input::get('company');
                    $ext = $file->getExtension();

                    $path = $file->getRealPath();
                    $name = $file->getClientOriginalName();
                    $size = $file->getSize();
                    $mime = $file->getMimeType();
                    $contents = file_get_contents($file);
                    $extension = pathinfo($name, PATHINFO_EXTENSION); // file extension only

                    $get_selected = DB::table('fieldnames')->select('fieldname')->where('status','=','1')->orderBy('type','desc')->orderBy('updated_at', 'asc')->get();
                    $get_header = DB::table('fieldnames')->select('fieldname')->where('type', '=', '1')->orderBy('type','desc')->orderBy('updated_at', 'asc')->get();
                    
                    $get_count = count($get_selected);
                    $header_count = count($get_header);



                    if ($extension == "pdf") {
                        $response = $this->mapsRepo->upload($name, $path, $size, $mime, $contents, $comp);
                    } else if ($extension == "xls" || $extension == "xlsx") {



                        if (empty($ext) or is_null($ext)) {
                            preg_match_all('/.(\w{3,4})/', $name, $match);
                            if (isset($match[0])) {
                                $ext = trim($match[0][1], '.');
                            }
                        }

                        $filename  = sha1(time().time()).".{$extension}";

                        $attributes = [
                            'filename'          => $filename,
                            'original_filename' => $file->getClientOriginalName(),
                            'extension'         => $extension,
                            'mime_type'         => $file->getClientMimeType(),
                            'action_by'         => $this->get_auth_user_id()
                        ];

                        $s3_response = $this->uploadsRepo->upload($this->s3, $file, $attributes, true);


                        if ($s3_response['status']) {
            
                            $url = $this->uploadsRepo->getUrl($this->s3, $s3_response['data']['key']);

                            $test_obj = new MapsModel();
                            $test_obj->file_name = $name;
                            $test_obj->mime_type = $mime;
                            $test_obj->company = $comp;
                            $test_obj->file_size = $size;
                            $test_obj->temp_path = $url;
                            $test_obj->save();

                            $response = ['status' => true, 'message' => 'Excel File Uploaded', 'company' => $comp, 'ext' => $extension, 'get_selected' => $get_selected, 'get_count' => $get_count, 'get_header' => $get_header, 'header_count' => $header_count, 'file_url'=> $url];
                            
                        }

                    } else {
                        $response = ['status' => false, 'message' => 'Invalid File. Please upload pdf or excel'];

                    }

                } else if (Input::get('company')==null) {

                    $response = ['status' => false, 'message' => 'Please enter company name'];

                } else {
                    $response = ['status' => false, 'message' => 'Please upload a file'];

                }

            }

        }
        return Response::json($response);

    }


    public function submit_map()
    {
        
         $response = ['status' => false, 'message' => 'Invalid request'];
         
         if (Request::ajax()) {

            if (Request::isMethod('POST')) {
                $form_data = Input::get('data');
                parse_str($form_data, $form);

                $total = Input::get('total');
                $query = Input::get('arr');
                $lines = Input::get('numlines');
                $company = Input::get('comp');
                $extension = Input::get('ext');
                $myArray = explode(',', $query);
                
                $string = '';
                $input = '';
                for ($i = 0; $i < $total; $i++) {
                  $result = $myArray[$i];
                 
                  $data[$result] = $form[$result];
                  
                  $input .= $data[$result].",";
                 
                }
                $arr = explode(',',$input);
                
                
                $response = $this->mapsRepo->submit_map($arr, $total, $company, $extension);
                  
            }
        }

       return Response::json($response);
    }


    public function update_modal()
    {
        
        $response = ['status' => false, 'message' => 'Invalid request'];
            if (Request::ajax()) {

                if (Request::isMethod('POST')) {

                    $data = Request::all();

                    $company_input = $data['input_comp'];
                    $company_saved = $data['saved_comp'];
                    $filename = $data['name'];
                    
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);

                    $query = DB::table('mappings')->where('company','=', $company_saved)->update(['company' => $company_input]);
                    
                    if ($extension == 'pdf') {
                        $pdf_old =  '/var/www/jedi/public/PDFFiles/'.$company_saved.'.pdf';
                        $pdf_new =  '/var/www/jedi/public/PDFFiles/'.$company_input.'.pdf';

                        $txt_old =  '/var/www/jedi/public/TextFiles/'.$company_saved.'.txt';
                        $txt_new =  '/var/www/jedi/public/TextFiles/'.$company_input.'.txt';

                        $xml_old =  '/var/www/jedi/public/XMLFiles/'.$company_saved.'.xml';
                        $xml_new =  '/var/www/jedi/public/XMLFiles/'.$company_input.'.xml';

                        File::move($pdf_old, $pdf_new);
                        File::move($txt_old, $txt_new);
                        File::move($xml_old, $xml_new);

                        
                        $response = ['status' => true, 'message' => 'Successfully Updated'];
                    } else if ($extension == 'xls' || $extension == 'xlsx') {
                        $excel_old = '/var/www/jedi/public/ExcelFiles/'.$company_saved.'.'.$extension;
                        $excel_new = '/var/www/jedi/public/ExcelFiles/'.$company_input.'.'.$extension;

                        $xml_old =  '/var/www/jedi/public/XMLFiles/'.$company_saved.'.xml';
                        $xml_new =  '/var/www/jedi/public/XMLFiles/'.$company_input.'.xml';

                        File::move($excel_old, $excel_new);
                        File::move($xml_old, $xml_new);

                        $response = ['status' => true, 'message' => 'Successfully Updated'];

                    }
                }
            }
        return Response::json($response);
    }


    public function delete_map()
    {
        $response = ['status' => false, 'message' => 'Invalid request'];
        if (Request::ajax()) {

            if (Request::isMethod('POST')) {
                $data = Request::all();

                $company = $data['comp'];
                $name = $data['name'];

                $mime = $data['mime'];
                $extension = pathinfo($name, PATHINFO_EXTENSION);

                DB::table('mappings')->where('company', '=', $company)->delete(); //delete record from db

                $file_pdf = '/var/www/jedi/public/PDFFiles/'.$company.".pdf";
                $file_txt = '/var/www/jedi/public/TextFiles/'.$company.".txt";
                $file_xml = '/var/www/jedi/public/XMLFiles/'.$company.".xml";
                
                $file_excel = '/var/www/jedi/public/ExcelFiles/'.$company.".".$extension;
                
                if ($extension == 'pdf') {
                    if (File::exists($file_pdf)) {

                        File::delete($file_pdf); 

                            if (File::exists($file_txt)) {
                                File::delete($file_txt); 
                            }

                            if (File::exists($file_xml)) {
                                File::delete($file_xml); 
                            }
                          $response = ['status' => true, 'message' => 'Successfully deleted'];
                        } 
                } else if ($extension == 'xls' || $extension == 'xlsx') {
                    
                    if (File::exists($file_excel)) {
                        File::delete($file_xml); 

                        File::delete($file_excel);
                        $response = ['status' => true, 'message' => 'Successfully deleted'];
                    
                    }
                }
                        
            }
        }
       return Response::json($response);
    }



    public function view_xml() 
    {
       
      $response = ['status' => false, 'message' => 'Invalid request'];
        if (Request::ajax()) {

          if (Request::isMethod('POST')) {
             $data = Request::all();

                $company = $data['comp'];
                $name = $data['filename'];
                $mime = $data['filemime'];
                $path_xml = '/var/www/jedi/public/XMLFiles/'.$company . '.xml';
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                
                if (File::exists($path_xml)) {

                    if ($extension == 'pdf') {
                        $path_pdf = '/var/www/jedi/public/TextFiles/'.$company . '.txt';
                        $html = file_get_contents($path_pdf); // view pdf
                    }

                    $url = DB::table('mappings')->select('temp_path')->where('company', '=', $company)->get();

                    $xml = simplexml_load_file($path_xml);
                    $headers = $xml->content->consumer->stores->store->headers->children();
                    $items = $xml->content->consumer->stores->store->items->children();
                    
                    DB::table('fieldnames')->update(array('status' => 0, 'type' => 0 )); // update fieldname status and type to 0

                    $h_fieldname = '';
                    $h_value = '';

                    foreach ($headers as $fieldname => $value) {
                        $date = date('Y-m-d H:i:s:ms');
                        DB::table('fieldnames')->where('fieldname', '=', $fieldname)->update(array('status' => 1, 'type' => 1, 'updated_at' => $date));
                        $h_fieldname .= $fieldname.',';
                        $h_value .= $value.',';
                        sleep(1);
                    }

                    $i_fieldname = '';
                    $i_value = '';
                    foreach ($items as $fieldname => $value) {
                        $date = date('Y-m-d H:i:s');
                        DB::table('fieldnames')->where('fieldname', '=', $fieldname)->update(array('status' => 1, 'updated_at' => $date));
                        $i_fieldname .= $fieldname.',';
                        $i_value .= $value.',';
                        sleep(1);
                    }
                    
                    if ($extension == 'pdf') {
                        $response = ['status' => true, 'html' => $html, 'company' => $company, 'header_value' => $h_value, 'header_fieldname' => $h_fieldname, 'item_value' => $i_value, 'item_fieldname' => $i_fieldname, 'ext' => $extension];
                    } else if ($extension == 'xls' || $extension == 'xlsx') {
                        $response = ['status' => true, 'company' => $company, 'header_value' => $h_value, 'header_fieldname' => $h_fieldname, 'item_value' => $i_value, 'item_fieldname' => $i_fieldname, 'ext' => $extension, 'url' => $url];
                    }
                }
            }
        }
       return Response::json($response);

    }


    public function refresh() 
    {
       
    $response = ['status' => false, 'message' => 'Invalid request'];
        if (Request::ajax()) {

            if (Request::isMethod('POST')) {
                $data = Request::all();

                $company = $data['comp'];
                $extension = $data['ext'];

                $path_xml = '/var/www/jedi/public/XMLFiles/'.$company . '.xml';
                    
                if (File::exists($path_xml)) {
                    
                    if ($extension == 'pdf') {
                    $path_pdf = '/var/www/jedi/public/TextFiles/'.$company . '.txt';
                    $html = file_get_contents($path_pdf); // view pdf
                    }

                    $xml = simplexml_load_file($path_xml);
                    $headers = $xml->content->consumer->stores->store->headers->children();
                    $items = $xml->content->consumer->stores->store->items->children();
                    

                    $h_fieldname = '';
                    $h_value = '';
                    foreach ($headers as $fieldname => $value) {
                        $h_fieldname .= $fieldname.',';
                        $h_value .= $value.',';
                    }
                    $i_fieldname = '';
                    $i_value = '';
                    foreach ($items as $fieldname => $value) {
                        $i_fieldname .= $fieldname.',';
                        $i_value .= $value.',';
                    }
                    
                    $query = DB::table('fieldnames')->select('fieldname')->where('status','=','1')->orderBy('type','desc')->orderBy('updated_at', 'asc')->get();
                    $selected = DB::table('fieldnames')->select('fieldname')->where('type', '=', '1')->orderBy('type','desc')->orderBy('updated_at', 'asc')->get();
                        
                    $count = count($query);
                    $header_count = count($selected);

                    $response = ['status' => true, 'company' => $company, 'header_value' => $h_value, 'header_fieldname' => $h_fieldname, 'item_value' => $i_value, 'item_fieldname' => $i_fieldname, 'query' => $query, 'count'=> $count, 'selected' => $selected, 'header_count'=> $header_count];
                }

            }
        }
       return Response::json($response);

    }



    public function view_xml_window() 
    {
       
      $response = ['status' => false, 'message' => 'Invalid request'];
        if (Request::ajax()) {

          if (Request::isMethod('POST')) {
             $data = Request::all();

                $company = $data['comp'];
                
                $path_xml = '/var/www/jedi/public/XMLFiles/'.$company . '.xml';
                
                if (File::exists($path_xml)) {

                $xml = simplexml_load_file($path_xml);
                $headers = $xml->content->consumer->stores->store->headers->children();
                $items = $xml->content->consumer->stores->store->items->children();

                $h_fieldname = '';
                $h_value = '';
                foreach ($headers as $fieldname => $value) {
                    $h_fieldname .= $fieldname.',';
                    $h_value .= $value.',';
                }
                $i_fieldname = '';
                $i_value = '';
                foreach ($items as $fieldname => $value) {
                    $i_fieldname .= $fieldname.',';
                    $i_value .= $value.',';
                }
              
                $response = ['status' => true,  'company'=> $company, 'header_field' => $h_fieldname, 'header_value' => $h_value, 'item_field' => $i_fieldname, 'item_value' => $i_value];
                }

          }
        }
       return Response::json($response);

    }

   
    public function view_all_data() 
    {
      $response = ['status' => false, 'message' => 'Invalid request'];
        if (Request::ajax()) {

          if (Request::isMethod('POST')) {
             $data = Request::all();

                $company = $data['comp'];
                $extension = $data['extension'];

                $query = DB::table('mappings')->where('company', '=', $company)->get();

                if ($query) {
                
                  DB::table('mappings')->where('company', '=', $company)->delete(); //delete record from db
                  
                  if ($extension == 'pdf') {

                      $file_pdf = '/var/www/jedi/public/PDFFiles/'.$company."." .$extension;
                      $file_txt = '/var/www/jedi/public/TextFiles/'.$company.".txt";
                     
                      if (File::exists($file_pdf)) {

                          File::delete($file_pdf); // delete pdf
                          File::delete($file_txt); //delete txt

                          $response = ['status' => true, 'message' => 'Successfully deleted'];
                      }

                  } else  if ($extension == 'xls' || $extension == 'xlsx') {
                      $file_excel = '/var/www/jedi/public/ExcelFiles/'.$company.".".$extension;
                      
                      if (File::exists($file_excel)) {

                          File::delete($file_excel); //delete excel
                          $response = ['status' => true, 'message' => 'Successfully deleted'];

                      }

                  }


                }
               
          }
        }
       return Response::json($response);
    }


    public function create_fieldname() 
    {
        $response = ['status' => false, 'message' => 'Invalid request'];
        if (Request::ajax()) {

          if (Request::isMethod('POST')) {
             $data = Request::all();

                $name = $data['fieldname'];
                // dd($name);
                $response = $this->mapsRepo->create_fieldname($name);
                
                
          }
        } 
       return Response::json($response);

    }


    public function delete_fieldname()
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        if (Request::ajax()) {

            if (Request::isMethod('POST')) {
                $data = Request::all();
                
                $name = $data['fieldname'];

                $query = DB::table('fieldnames')->where('fieldname', '=', $name)->get();

                if ($query) {
                  
                    DB::table('fieldnames')->where('fieldname', '=', $name)->delete(); //delete record from db
                    
                    $response = ['status' => true, 'message' => 'Successfully deleted'];

                } 

            }

        } 

       return Response::json($response);

    }


    public function edit_fieldname()
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        if (Request::ajax()) {

            if (Request::isMethod('POST')) {
                $data = Request::all();
                
                $name = $data['saved'];
                $name_update = $data['input'];
                
                $query = DB::table('fieldnames')->where('fieldname','=', $name)->update(['fieldname' => $name_update]);
                
                if ($query) {
                    $response = ['status' => true, 'message' => 'Successfully updated'];
                } 


            }

        } 

       return Response::json($response);

    }

    public function add_fieldname()
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        if (Request::ajax()) {

            if (Request::isMethod('POST')) {
                $data = Request::all();
                
                $name = $data['name'];
                $date = date('Y-m-d H:i:s');
                $query = DB::table('fieldnames')->where('fieldname','=', $name)->update(['status' => '1', 'updated_at' => $date]);
                if ($query) {
                    $response = ['status' => true, 'message' => 'Successfully addedd'];
                }
            }
        } 

       return Response::json($response);

    }


    public function remove_fieldname()
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        if (Request::ajax()) {

            if (Request::isMethod('POST')) {
                $data = Request::all();
                
                $name = $data['name'];
                
                $query = DB::table('fieldnames')->where('fieldname','=', $name)->update(['status' => '0']);
                if ($query) {
                    $response = ['status' => true, 'message' => 'Successfully removed'];
                }
            }
        } 

       return Response::json($response);

    }

    public function get_selected()
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        if (Request::ajax()) {

            if (Request::isMethod('POST')) {
                $data = Request::all();
                $company = $data['comp'];
                $selected = $data['selected'];
                $arr  =explode(",",$selected);
                $len = count($arr) - 1;

                for ($i=0; $i<$len;$i++) {
                    $result = $arr[$i];
                    
                    $update_type = DB::table('fieldnames')->where('fieldname', '=', $result)->update(['type' => 1]);
                } 


                $update = DB::table('mappings')->where('company', '=', $company)->update(['status' => 'fieldname selected']);

                $get_selected = DB::table('fieldnames')->select('fieldname')->where('status','=','1')->orderBy('type','desc')->orderBy('updated_at', 'asc')->get();
                
                $get_count = count($get_selected);
                $response = ['status' =>true, 'company' => $company, 'get_selected' => $get_selected, 'get_count' => $get_count];


                
            }
        } 

       return Response::json($response);

    }

    public function remove_header() 
    {
       $response = ['status' => false, 'message' => 'Invalid request'];

        if (Request::ajax()) {

            if (Request::isMethod('POST')) {
                $data = Request::all();

                $query = DB::table('fieldnames')->where('type', '=', '1')->update(['type' => 0]);
                $get_selected = DB::table('fieldnames')->select('fieldname')->where('status','=','1')->orderBy('type','desc')->orderBy('updated_at', 'asc')->get();
                
                $get_count = count($get_selected);

                $response = ['status' =>true, 'get_selected' => $get_selected, 'total' => $get_count];

            }
        }
       return Response::json($response);

    }


    public function zip() 
    {
       $response = ['status' => false, 'message' => 'Invalid request'];

        if (Request::ajax()) {

            if (Request::isMethod('POST')) {
                $data = Request::all();

                $array = $data['arr'];
                $count = count($array);
                $zname = $data['zname'];
               

                $zip = new ZipArchive();
                $zipname = $zname.".zip";
                $zip->open($zipname,  ZipArchive::CREATE);
                
                for ($i=0; $i<$count-1;$i++) {
                    $path = "/var/www/jedi/public/XMLFiles/".$array[$i];
                    if(file_exists($path)){
                        $zip->addFromString(basename($path),  file_get_contents($path));  
                    }
                    else{
                        echo "file does not exist";
                    }
                }
                $zip->close();

                $zipPath = 'http://jedi.dev/'.$zipname;

                $response = ['status' =>true, 'message' => 'Successfully zipped','array' => $array, 'count' => $count, 'zip' => $zipPath];

            }
        }
       return Response::json($response);

    }


    public function download() 
    {
       $response = ['status' => false, 'message' => 'Invalid request'];

        if (Request::ajax()) {

            if (Request::isMethod('POST')) {
                $data = Request::all();

                $company = $data['comp'];
                $fname = $data['file'];
               

                $zip = new ZipArchive();
                $zipname = $company.".zip";
                $zip->open($zipname,  ZipArchive::CREATE);
                
                
                $path = "/var/www/jedi/public/XMLFiles/".$fname;
                if(file_exists($path)){
                    $zip->addFromString(basename($path),  file_get_contents($path));  
                }
                else{
                    echo "file does not exist";
                }
                
                $zip->close();

                $zipPath = 'http://jedi.dev/'.$zipname;

                $response = ['status' =>true, 'message' => 'Success', 'zip' => $zipPath];

            }
        }
       return Response::json($response);

    }

    
}