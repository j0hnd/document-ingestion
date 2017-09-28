<?php
namespace Jedi\Features\Maps\Repositories;

use Jedi\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Model;
use Jedi\Repositories\ValidatorInterface;

use Jedi\Features\Maps\Models\MapsModel;
use Jedi\Features\Maps\Models\FieldnamesModel;


use DB, Storage, Response, File;

class MapsRepository extends EloquentRepository implements MapsInterface
{
    protected $validator;

    protected $rules = [];

    protected $messages = [];

    
    public function __construct(Model $model, ValidatorInterface $validator)
    {
        parent::__construct($model);

        $this->validator = $validator;
    }

    public function foobar()
    {
        return 'Name';
    }

    public function upload($name, $path, $size, $mime, $contents, $comp)
    {
          $response = ['status' => false, 'message' => 'Invalid requestasd'];

          try {

            $files = DB::table('mappings')->where('company', $comp)->get();
            
            if ($files) {
                
                $response = ['status' => false, 'message' => 'Company: '.$comp .' already exist'];

            } else {
                
                if (strlen($name) > 0) {
                    $test_obj = new MapsModel();
                    $test_obj->file_name = $name;
                    $test_obj->mime_type = $mime;
                    $test_obj->company = $comp;
                    $test_obj->file_size = $size;
                    $test_obj->temp_path = $path;
                   

                    if ($test_obj->save()) {
                        $file_name = pathinfo($name, PATHINFO_FILENAME); // file name without extension
                        $extension = pathinfo($name, PATHINFO_EXTENSION); // file extension only

                        $get_selected = DB::table('fieldnames')->select('fieldname')->where('status','=','1')->orderBy('type','desc')->orderBy('updated_at', 'asc')->get();
                        $get_header = DB::table('fieldnames')->select('fieldname')->where('type', '=', '1')->orderBy('type','desc')->orderBy('updated_at', 'asc')->get();
                        
                        $get_count = count($get_selected);
                        $header_count = count($get_header);

                        if ($extension == 'pdf') {
                            
                            $fname = $comp.".pdf";
                            Storage::disk('local')->put($fname, $contents);

                            $path_pdf_stor = '/var/www/jedi/storage/app/'.$fname;
                            $path_pdf_public = '/var/www/jedi/public/PDFFiles/'.$fname;

                            File::move($path_pdf_stor, $path_pdf_public); 

                            $parser = new \Smalot\PdfParser\Parser();
                            $pdf    = $parser->parseFile($path_pdf_public);
                             
                            $text = $pdf->getText();
                            $path_txt = '/var/www/jedi/public/TextFiles/'.$comp . '.txt';
                            File::put($path_txt, $text);


                            if (File::exists($path_txt)) {

                            $html = file_get_contents($path_txt);

                            
                            $response = ['status' => true, 'message' => 'Pdf File Uploaded', 'html' => $html, 'company' => $comp, 'ext' => $extension, 'get_selected' => $get_selected, 'get_count' => $get_count, 'get_header' => $get_header, 'header_count' => $header_count];
                            
                            }

                        } 

                     }
                       
                }
    
            }
            
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

       return $response;
       
    }


    public function submit_map($arr, $total, $company, $extension)
    {
          $response = ['status' => false, 'message' => 'Invalid request'];
        
        try {
                $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" standalone="yes" ?><template></template>');

                    $xml->addChild('companyname', $company);
                    $xml->addChild('output', 'LIVE');
                    $xml->addChild('filetype', $extension);
                    
                    $content = $xml->addchild('content');
                    $consumer = $content->addChild('consumer');

                    $stores = $consumer->addChild('stores');
                    $store = $stores->addChild('store');
                    $headers = $store->addChild('headers');

                    $query_header = DB::table('fieldnames')->where('type', '=' , 1)->orderBy('updated_at', 'asc')->get();

                    $i = 0;
                    foreach ($query_header as $row) {
                        $field = $row->fieldname;
                        
                        $result = $arr[$i];
                        $headers->addChild($field, $result);
                        $i++;
                        
                    }
                    $header_count = $i;
                   
                    $items = $store->addChild('items');
                    $query_item = DB::table('fieldnames')->where('status', '=' , 1)->where('type','=', 0)->orderBy('updated_at', 'asc')->get();

                    
                    if ($query_item) {
                        foreach ($query_item as $key) {
                           $value = $key->fieldname;
                           $results = $arr[$header_count];
                            $items->addChild($value, $results);
                            $header_count++;
                        }
                    }


                $xml_file = '/var/www/jedi/public/XMLFiles/'.$company.'.xml';
                
                if ($xml->saveXML($xml_file)) {
                    
                    $date = date('Y-m-d H:i:s:ms');
                    DB::table('mappings')->where('company', $company)
                                        ->update(['status' => 'mapped', 'updated_at' => $date]);

                    $response = ['status' => true, 'message' => 'XML File saved'];
                    

                } else {
                    $response = ['status' => false, 'message' => 'XML File not saved'];
                    
                }
           
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }


    public function create_fieldname($name)
    {
          $response = ['status' => false, 'message' => 'Invalid request'];
        
        try {
            $field = DB::table('fieldnames')->where('fieldname', $name)->get();
            
            if ($field) {
                
                $response = ['status' => false, 'message' => 'Fieldname: '.$name .' already exist'];

            } else {

                if (strlen($name) > 0) {
                        $test_obj = new FieldnamesModel();
                        $test_obj->fieldname = $name;
                        $test_obj->status = '0';
                        
                        if ($test_obj->save()) {
                            $response = ['status' => true, 'message' => 'Success'];

                        }
                }
            }
            
            
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }
}