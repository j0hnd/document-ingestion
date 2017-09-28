<?php namespace Jedi\Features\Documents\Controllers;

use Jedi\Controllers\BaseController;

use Jedi\Features\Documents\Repositories\UploadsInterface;
use Jedi\Features\Documents\Repositories\DocumentsInterface;
use Jedi\Features\Documents\Repositories\DocumentMetasInterface;
use Jedi\Features\Documents\Repositories\DocumentDetailsInterface;

use Jedi\Features\Documents\Repositories\BatchInterface;
use Jedi\Features\Sites\Repositories\SitesInterface;
use Nathanmac\Utilities\Parser\Facades\Parser;
use Redirect , Request , Input, Response;
use AWS, Excel, Event, Session, ZipArchive;
use Smalot;

class DocumentsController extends BaseController
{
    protected $batchRepo;
    protected $uploadRepo;
    protected $documentRepo;
    protected $sitesRepo;
    protected $metaRepo;
    protected $detailsRepo;

    private $s3;


    public function __construct(UploadsInterface $uploadRepo, DocumentsInterface $documentRepo,
                                BatchInterface $batchRepo, SitesInterface $sitesRepo,
                                DocumentMetasInterface $metaRepo, DocumentDetailsInterface $detailsRepo)
    {
        $this->batchRepo    = $batchRepo;
        $this->uploadRepo   = $uploadRepo;
        $this->documentRepo = $documentRepo;
        $this->sitesRepo    = $sitesRepo;
        $this->metaRepo     = $metaRepo;
        $this->detailsRepo  = $detailsRepo;

        $this->s3 = AWS::get('s3');

        parent::__construct();
    }

    public function index()
    {
        $sites_obj = $this->sitesRepo->get_all_active_sites();

        $sites = ['' => 'Please Select Input Processor'];

        if ($sites_obj->count()) {
            foreach ($sites_obj->get() as $_sites) {
                $sites[$_sites->site_id] = $_sites->site_name;
            }
        }

        $this->views['title']     = 'Upload Documents';
        $this->views['sites']     = $sites;
        $this->views['action_by'] = $this->get_auth_user_id();
        $this->views['output_destinations'] = $this->documentRepo->get_output_destinations();
        $this->views['upload_name_prefix']  = config('upload.upload_name_prefix');

        return view('Documents::uploads.index')->with($this->views);
    }

    public function uploads()
    {
    	$s3_response   = ['status' => false, 'message' => 'Invalid request'];
        $response_code = 400;

        $validate = $this->uploadRepo->validate_inputs(Input::all());

        if (!$validate['status']) {
            return Response::json([['status' => false, 'message' => $validate['errors'][0]], $response_code]);
        }

    	if (Input::hasFile('files')) {
    		$file = Input::file('files');
            $checkIfExists = $this->documentRepo->check_if_file_exists($file);
//            dd($file);
            if($checkIfExists['status']){ //file is existing
                $s3_response['message'] = $checkIfExists['message'];
            }else{
                $extension = $file[0]->getExtension();

                if (empty($extension) or is_null($extension)) {
                    preg_match_all('/.(\w{3,4})/', $file[0]->getClientOriginalName(), $match);
                    if (isset($match[0])) {
                        $extension = trim($match[0][1], '.');
                    }
                }

                $filename  = sha1(time().time()).".{$extension}";

                $attributes = [
                    'filename'          => $filename,
                    'original_filename' => $file[0]->getClientOriginalName(),
                    'extension'         => $extension,
                    'mime_type'         => $file[0]->getMimeType(),
                    'action_by'         => $this->get_auth_user_id()
                ];

                $s3_response = $this->uploadRepo->upload($this->s3, $file[0], $attributes);

                if ($s3_response['status']) {
//                // convert files to images and store locally
                    $images = null;
                    if ($extension == 'pdf' or $file[0]->getMimeType() == 'application/pdf') {
                        $images = $this->documentRepo->convert_pdf_to_image($_FILES['files']['tmp_name'][0], $attributes['original_filename']);
                    }

                    // save file details
                    $s3_response = $this->documentRepo->save([
                        'filename'    => $s3_response['data']['original_filename'],
                        'filesize'    => $s3_response['data']['filesize'],
                        'mime_type'   => $s3_response['data']['mime_type'],
                        's3_key_name' => $s3_response['data']['key'],
                        'action_by'   => $this->get_auth_user_id(),
                        'images'      => null,
                        'image_count' => count($images)
                    ]);

                    // copy images to s3
                    //$s3_response['copy'] = $this->uploadRepo->copy_image($this->s3, $images);
                }
            }
    	}

    	return Response::json($s3_response);
    }

    public function save()
    {
        $response = ['status' => false, 'message' => 'Error saving document'];

        if (Request::ajax()) {

            if (Request::method('POST')) {

                $inputs = Input::all();

                $validated = $this->batchRepo->validate_inputs([
                    'template_name'   => $inputs['template_name'],
                    'input_processor' => $inputs['input_processor'],
                    'destination'     => $inputs['destination'],
                ]);

                if (!$validated['status']) {
                    return Response::json(['status' => false, 'message' => $validated['errors']]);
                }

                $batch = $this->batchRepo->save([
                    'upload_name'           => $inputs['template_name'],
                    'site_id'               => $inputs['input_processor'],
                    'output_destination_id' => $inputs['destination'],
                    'action_by'             => $inputs['action_by']
                ]);

                if ($batch['status']) {
                    foreach ($inputs['id'] as $_id) {
                        $response = $this->documentRepo->reference_document_to_batch($_id, $batch['data']['id'], $inputs['action_by']);
                    }

                    Session::forget('id');
                }
            }

        }

        return Response::json($response);
    }

    public function list_documents($batch_id)
    {
        $documents = $this->metaRepo->get_document_files($batch_id);

        $this->views['title']     = 'Documents List';
        $this->views['documents'] = $documents;

        $this->batchRepo->update_batch_status($batch_id, $this->get_auth_user_id(), $documents);

        return view('Documents::process.list')->with($this->views);
    }

    public function process($document_id, $document_meta_id)
    {
        $this->views['title']     = "Process Document";
        $document                 = $this->documentRepo->get_document_details($document_id, $document_meta_id);
        $this->views['document']  = $document;
        $this->views['meta']      = $this->metaRepo->get_document_meta_details($document_meta_id);
        $this->views['action_by'] = $this->get_auth_user_id();

        if($document['data']['file_type'] == 'XLS'){
            $this->views['xls_file'] = $this->uploadRepo->getUrl($this->s3, $document['data']['s3_key_name']);
        }

        return view('Documents::process.document-details')->with($this->views);
    }

    public function update_batch_status($batch_id)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {

            if (Request::ajax()) {
                if (Request::method('POST')) {

                    $data = ['status' => Input::get('status'), 'updated_by' => $this->get_auth_user_id()];
                    $this->produce_xml($batch_id);
                    //dd($batch_id);
//                    exit;
                    if ($this->batchRepo->update($batch_id, $data)) {

                        $batch = $this->documentRepo->get_batch();
                        $html  =  view('Documents::partials.queue-list')->with(['batch' => $batch])->render();
                        $response = ['status' => true, 'message' => 'Batch updated', 'data' => ['html' => $html]];

                        if (Input::get('status') == 'Send') {
                            $result = $this->produce_xml($batch_id);
                            $response['zip']    = $result['zip'];
                            $response['folder_name'] = $result['folder_name'];
                        }
                    }

                }
            }

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return Response::json($response);
    }

    public function update_document_process($document_details_id)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {

            if (Request::ajax()) {

                if (Request::method('POST')) {
                    $status = Input::get('status');

                    switch ($status)
                    {
                        case "reject": $status = DOCUMENT_STATUS_REJECT; break;
                        case "accept": $status = DOCUMENT_STATUS_ACCEPT; break;
                        case "send":   $status = DOCUMENT_STATUS_SEND; break;
                    }

                    $data = ['status' => $status, 'checked_by' => $this->get_auth_user_id()];
                    $result = $this->detailsRepo->update_document($document_details_id, $data);

                    if ($result) {
                        $response = ['status' => true, 'message' => 'Document updated'];
                    }
                }

            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return Response::json($response);
    }

    private function get_mime_type($filename)
    {
    	$file = explode('.', $filename);
    	$ext  = strtolower(end($file));

    	switch ($ext) {
    		case "pdf":
    			$mime_type = 'application/pdf';
    			break;

            case "xls":
                $mime_type = 'application/vnd.ms-excel';
                break;

            case "xlsx":
                $mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
    	}

    	return $mime_type;
    }

    //test
    public function produce_xml($batch_id = null)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $meta_ids = $this->batchRepo->get_meta_ids($batch_id);
        $details = null;

//        $counterTwo = 0;
//        $code = '';
//        $desc = '';
//        $qty = '';
//        $price = '';
//        $uom = '';

        // get filename
        $path_info = pathinfo($meta_ids['data'][0]['filename']);

        $today     = new \DateTime();
        $timestamp = $today->format('mdYHis');

        $filename  = str_replace(' ', '', $path_info['filename']);
        $zip_file  = str_replace('.', '', $filename);
        $zip_file  = "{$zip_file}-{$timestamp}.zip";
        $sender    = explode(' ', $path_info['filename']);

        // get PO date
        $po_date_arr = explode('.', $path_info['filename']);
        $po_date_arr1 = explode('-', $po_date_arr[1]);
        $po_date = \DateTime::createFromFormat("mdY", $po_date_arr1[0]);

        $loccode_obj = config('loccode');

        // dyna's calculated expected delivery date
        $friday = $today->modify('friday this week');

        if ($meta_ids['status']) {
            foreach ($meta_ids['data'] as $meta_id) {
                $details[$meta_id['document_name']] = $this->metaRepo->get_document_meta_details($meta_id['id']);
            }
        }

        $lineitems = [];

        foreach($details as $key => $data){
            $lineSeparate = [];

            foreach($data['data'] as $index => $items){
                $set = [];
                foreach ($items as $value) {
                    $_line = [
                        $value['key'] => $value['value']
                    ];

                    $set = array_merge($set , $_line);
                }

                array_push($lineSeparate , $set);
            }

            $lineitems[$key] = $lineSeparate;
        }

        try {

            $filename = str_replace('.', '', $filename);
            $filename .= '-'.$timestamp;
            $destination_folder = config('upload.xml_folder').'/'.$filename;

            if (!file_exists($destination_folder)) {
                mkdir($destination_folder, 0777, true);
            }

            foreach ($lineitems as $key => $item) {
                $loccode = 'N/A';

                if ($sender[0] == 'DYNA') {
                    if (strpos($key, 'PC') === false) {
                    } else {
                        $loccode = $loccode_obj[$sender[0]]['Pasig']['Consumer'];
                    }

                    if (strpos($key, 'PO') === false) {
                    } else {
                        $loccode = $loccode_obj[$sender[0]]['Pasig']['OTC'];
                    }

                    if (strpos($key, 'MC') === false) {
                    } else {
                        $loccode = $loccode_obj[$sender[0]]['Manila']['Consumer'];
                    }

                    if (strpos($key, 'MO') === false) {
                    } else {
                        $loccode = $loccode_obj[$sender[0]]['Manila']['OTC'];
                    }
                }

                $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" standalone="yes" ?><Orders></Orders>');

                $xml->addChild('messagetype', 'Orders');
                $xml->addChild('sender', $sender[0]);
                $xml->addChild('docnum', $key);
                $xml->addChild('loccode', $loccode);
                $xml->addChild('podate', $po_date->format('m/d/Y'));
                $xml->addChild('deldate', $friday->format('m/d/Y'));

                $line_num = 1;

                foreach ($item as $index => $value) {
                    $lineitem = $xml->addChild('lineitems');
                    $lineitem->addChild('lineitemnum', $line_num);
                    $lineitem->addChild('itemnumber', isset($value['item_code']) ? $value['item_code'] : "N/A");
                    $lineitem->addChild('itemdescription', isset($value['item_description']) ? htmlspecialchars($value['item_description']) : "N/A");
                    $lineitem->addChild('quantity', isset($value['quantity']) ? $value['quantity'] : "0");
                    $lineitem->addChild('price', isset($value['jj_price']) ? $value['jj_price'] : "N/A");
                    $lineitem->addChild('uom', isset($value['uom']) ? $value['uom'] : "N/A");

                    $line_num++;
                }

                $xml_file = "{$destination_folder}/{$key}.xml";

                if ($xml->saveXML($xml_file)) {
                    $response = ['status' => true, 'zip' => $zip_file, 'folder_name' => $filename];
                }
            }
        } catch (\Exception $e) {
            $response = ['status' => false, 'message' => $e->getMessage()];
        }


        return $response;
    }

    public function download_output($zip_file, $source)
    {
        $zip = new \ZipArchive;

        $source = config('upload.xml_folder') . '/'. $source;

        if ($zip->open(config('upload.xml_folder') . '/' . $zip_file, ZipArchive::CREATE) === true) {

            // Copy all the files from the folder and place them in the archive.
            foreach (glob($source . '/*') as $filename) {
                $file = basename($filename);
                $zip->addFile($filename, $file);
            }

            $zip->close();

            $headers = array(
                'Content-Type' => 'application/octet-stream',
            );

            // Download .zip file.
            return Response::download(config('upload.xml_folder') . '/' . $zip_file, $zip_file, $headers);
        }
    }

    public function delete_document()
    {
        if (Request::ajax()) {
            if(Request::isMethod('POST')) {
                $input = Request::all();
                $input['action_by'] = $this->get_auth_user_id();
                //dd($input);
                $response = $this->documentRepo->delete_doc_uploaded($input); //soft delete only
            }
            return Response::json($response);
        }
    }

    public function test(){
        $xmlFile = file_get_contents(config('upload.tmp_folder').'robinsons.xml');
        $xml = simplexml_load_string($xmlFile, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);
        $company_name = $array['companyname'];
        $items = $array['content']['consumer']['stores'];
        $parser = new \Smalot\PdfParser\Parser();
        $x = [];
        foreach (array_values($items) as $data) {
            foreach (array_values($data) as $linenum) {
                foreach ($linenum as $line) {
                    $x[] = $line;
                }
            }
        }
        sort($x);
        $pdf    = $parser->parseFile(config('upload.tmp_folder').'po1.pdf');
        $text = $pdf->getText();
//        save content to text
        $file = config('upload.tmp_folder').'PO.txt';
        $newText = $text;
        file_put_contents($file, $newText);
        $handle = fopen($file, "r");
        if ($handle) {
            $i = 1;
            while (($line = fgets($handle)) !== false) {
                if(strpos($line,"GROSS")){
                    break;
                }
                if($i == '40'){
                    $po_num = $line;
                }
                if($i == '31'){
                    $loc_code = substr($line, 0, strrpos($line, '-'));
                }
                if($i == '43'){
                    $delivery_date = $line;
                }
                if (in_array($i, $x)) {
                    if($i == last($x)){
                        $items = $this->__extract_it($line);
                        echo 'DESCRIPTION: '.$items['description'];
                        echo 'SKU: '.$items['sku'].'<br>';
                        echo 'UOM: '.$items['uom'].'<br>';
                        echo 'QTY: '.$items['qty'].'<br>';
                        echo 'JJ: '.$items['jj'].'<br>';
                    }
                }
                if($i > last($x)){
                    $items = $this->__extract_it($line);
                    echo 'DESCRIPTION: '.$items['description'];
                    echo 'SKU: '.$items['sku'].'<br>';
                    echo 'UOM: '.$items['uom'].'<br>';
                    echo 'QTY: '.$items['qty'].'<br>';
                    echo 'JJ: '.$items['jj'].'<br>';
                }
                $i++;
            }
            echo json_encode(['companyname' => $company_name, 'deliverydate' => $delivery_date, 'loccode' => $loc_code, 'ponum' => $po_num]);
            fclose($handle);
            exit;
        } else {
            echo "Unable to read .txt file";
        }

    }

    private function __extract_it($data){
        $valueArray = explode(" ", $data);
//        dd($valueArray);
        $firstDesc = substr($valueArray[0],9 , strlen($valueArray[0]) - 8);
//        dd($firstDesc);

        $maxIndex = max(array_keys($valueArray));
        $quantityArrayKey = $maxIndex - 1;
        $desc = "";

        for($s = 1; $s < count($valueArray) - 2; $s++){
            $desc .= " ".$valueArray[$s];
        }
        $description = $firstDesc.$desc;
        $qty = $valueArray[$quantityArrayKey];
        $jjprice = explode('.',$valueArray[$maxIndex]);
        $length = strlen($jjprice[2]);
        $jj = substr($jjprice[2],2,$length - 2).'.'.substr($jjprice[3] , 0 , 2);
        $sku = substr($data, 0, 9);
        $uom = preg_replace('/[0-9]+/', '', substr($data, -5));

        $item['sku'] = $sku;
        $item['uom'] = $uom;
        $item['qty'] = $qty;
        $item['jj']  = $jj;
        $item['description'] =$description;
//        dd($item);
        return $item;

    }

}