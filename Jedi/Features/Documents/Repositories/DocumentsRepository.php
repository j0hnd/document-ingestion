<?php
namespace Jedi\Features\Documents\Repositories;

use Jedi\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Model;
use Jedi\Repositories\ValidatorInterface;

use Jedi\Features\Documents\Models\BatchModel;
use Jedi\Features\Documents\Models\DocumentsModel;
use Jedi\Features\Documents\Models\DocumentMetasModel;
use Jedi\Models\OutputDestinationsModel;

use DB, Imagick, Excel, PHPExcel_IOFactory, Smalot;
use Mockery\CountValidator\Exception;


class DocumentsRepository extends EloquentRepository implements DocumentsInterface
{
    protected $batchRepo;


    public function __construct(Model $model, ValidatorInterface $validator)
    {
        parent::__construct($model);

        $this->validator = $validator;

        $this->batchRepo = new BatchRepository(new BatchModel(), $validator);
        $this->metaRepo  = new DocumentMetasRepository(new DocumentMetasModel(), $validator);

    }

    public function get_batch($filters = null)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $batch = null;

        try {

            $batch_obj = DB::table('batch as b')
                ->select(DB::raw('
                    b.id as batch_id,
                    b.upload_name,
                    b.source,
                    b.status,
                    s.site_name,
                    count(dm.id) as file_count,
                    case when mime_type = "application/xml" then "XML" else "XLS" end as file_type,
                    o.output_destination_name,
                    b.created_at'
                ))
                ->join('documents as d', 'd.batch_id', '=', 'b.id')
                ->join('sites as s', 's.id', '=', 'b.site_id')
                ->join('output_destinations as o', 'o.id', '=', 'b.output_destination_id')
                ->leftjoin('document_metas as dm', 'dm.document_id', '=', 'd.id')
                ->where('b.is_active', 1)
                ->whereNotIn('b.status', ['Removed'])
                ->where('d.is_active', 1)
                ->orderBy('b.created_at', 'desc')
                ->groupBy('b.id');

            if(isset($filters) && !empty($filters)){
                if(!empty($filters['status'] && $filters['status'] != 'All')){
                    $batch_obj->where('b.status','=',$filters['status']);
                }
            }

            if ($batch_obj->count()) {
                foreach ($batch_obj->get() as $i => $row) {
                    $batch[$i]['batch_id']    = $row->batch_id;
                    $batch[$i]['upload_name'] = $row->upload_name;
                    $batch[$i]['output']      = $row->output_destination_name;
                    $batch[$i]['source']      = $row->source;
                    $batch[$i]['status']      = $row->status;
                    $batch[$i]['site_name']   = $row->site_name;
                    $batch[$i]['file_count']  = $row->file_count;
                    $batch[$i]['file_type']   = $row->file_type;
                    $batch[$i]['created_at']  = $row->created_at;
                }

                $response = ['status' => true, 'data' => $batch];
            }

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function save(array $inputs)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {
            DB::beginTransaction();

            $document_obj = new DocumentsModel();
            $document_obj->filename    = $inputs['filename'];
            $document_obj->filesize    = $inputs['filesize'];
            $document_obj->mime_type   = $inputs['mime_type'];
            $document_obj->s3_key_name = $inputs['s3_key_name'];
            $document_obj->image_count = $inputs['image_count'];
            $document_obj->is_active   = 0;

            if ($document_obj->save()) {
                $response['status']     = true;
                $response['message']    = '';
                $response['data']['id'] = $document_obj->id;
                $response['data']['filename']   = $inputs['filename'];

                DB::commit();

                // create logs
                $this->log([
                    [
                        'action' => SYSTEM_LOGS_ACTION_INSERT,
                        'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                        'new_value' => json_encode(['documents' => [
                            'filename'    => $inputs['filename'],
                            'filesize'    => $inputs['filesize'],
                            'mime_type'   => $inputs['mime_type'],
                            's3_key_name' => $inputs['s3_key_name'],
                            'action_by'   => $inputs['action_by']
                        ]])
                    ]
                ]);

            }
        } catch (\Exception $e) {
            $response['message'] = $this->parse_sql_error_message($e->getMessage());

            DB::rollback();

            $this->log([
                [
                    'action' => SYSTEM_LOGS_ACTION_ERROR,
                    'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode([
                        'error_msg' => $this->parse_sql_error_message($e->getMessage())
                    ])
                ]
            ]);
        }

        return $response;
    }

    public function reference_document_to_batch($document_id, $batch_id, $action_by)
    {
        $document_obj = DocumentsModel::where('id', $document_id);

        if ($document_obj->count()) {
            $response = $document_obj->update(['batch_id' => $batch_id, 'is_active' => 1]);

            if ($response) {
                $this->log([
                    [
                        'action' => SYSTEM_LOGS_ACTION_UPDATE,
                        'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                        'new_value' => json_encode(['documents' => ['batch_id' => $batch_id, 'is_active' => 1]]),
                        'action_by' => $action_by
                    ]
                ]);

                $response = ['status' => true, 'message' => 'Saved'];
            } else {
                $this->log([
                    [
                        'action' => SYSTEM_LOGS_ACTION_ERROR,
                        'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                        'new_value' => 'Error linking documents to batch { ID: '.$batch_id.'}',
                        'action_by' => $action_by
                    ]
                ]);

                $response = ['status' => false, 'message' => 'Error linking documents to batch'];
            }
        } else {
            $response = ['status' => false, 'message' => 'Can\'t find record'];
        }


        return $response;
    }

    public function get_output_destinations()
    {
        $output_obj = OutputDestinationsModel::where(['is_active' => 1])->orderBy('output_destination_name', 'asc');

        $output_arr = ['' => 'Please Select Output Destination'];

        if ($output_obj->count()) {
            foreach ($output_obj->get(['id', 'output_destination_name']) as $out) {
                $output_arr[$out->id] = $out->output_destination_name;
            }
        }

        return $output_arr;
    }

    public function convert_pdf_to_image($file, $filename)
    {
        $img = new Imagick($file);

        $images = null;
        $new_file_name_ref = explode('.', $filename);

        for ($i = 0; $i <= ($img->getNumberImages() - 1); $i++) {
            $_img = new Imagick($file.'['.$i.']');
            $_img->setImageResolution(300,300);
            $_img->setImageFormat('jpg');
            $_img->setImageCompressionQuality(90);
            $_img = $_img->flattenImages();

            $images[$i] = $new_file_name_ref[0].'-'.$i.'.jpg';

            $_img->writeImage(config('upload.pos_folder') . '/'.$images[$i]);
            $_img->clear();
            $_img->destroy();
        }

        $img->clear();
        $img->destroy();

        return $images;
    }
    
    public function extract($file, $data)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $rows = null;

        try {
            switch ($file['extension']) {
                case "xls":
                    $data_source = $file['dirname'] . '/' . $file['basename'];
                    $data['filename'] = $file['basename'];
                    $response = $this->map($data_source, $data);
                    break;

                default;
                    $response = $this->map_pdf($data['required_fields']['url'], $data['required_fields']['templates'], $data['document_id']);
                    break;
            }

            if (!is_null($rows)) {
                $response = ['status' => true, 'message' => '', 'data' => $response];
            }

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function get_required_fields($input_file_name, $attributes)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $fields = null;
        $po = null;

        $filename    = str_replace('-', '.', $input_file_name['filename']);
        $filename    = str_replace('_', '.', $filename);
        $filename    = explode('.', $filename);
        $po_date_obj = \DateTime::createFromFormat('mdY', $filename[1]);
        $po_date     = $po_date_obj->format('mdY');

        if ($attributes) {
            $po_number = $attributes['company'].$po_date;

            $fields['sheet_index'] = $attributes['sheet_index'];

            foreach ((array) $attributes as $key => $attribute) {

                if (is_object($attribute)) {

                    foreach ((array)$attribute as $key => $attr) {

                        foreach ($attr->stores as $stores) {
                            foreach ($stores as $store_name => $_attr) {

                                $fields['pos'][$key][strtolower($store_name)]['po_date']   = $filename[1];

                                if ($store_name == 'pasig') {
                                    if ($key == 'consumer') {
                                        $fields['pos'][$key][strtolower($store_name)]['po_number'] = "{$po_number}PC";
                                    } elseif ($key == 'otc') {
                                        $fields['pos'][$key][strtolower($store_name)]['po_number'] = "{$po_number}PO";
                                    }
                                } elseif ($store_name == 'manila') {
                                    if ($key == 'consumer') {
                                        $fields['pos'][$key][strtolower($store_name)]['po_number'] = "{$po_number}MC";
                                    } elseif ($key == 'otc') {
                                        $fields['pos'][$key][strtolower($store_name)]['po_number'] = "{$po_number}MO";
                                    }
                                }

                                if ($_attr->items) {
                                    foreach ($_attr->items as $items) {

                                        foreach ($items as $item) {
                                            $_item = (array) $item;
                                            $fields['pos'][$key][strtolower($store_name)]['keys'][] = ['key' => str_replace(' ', '_', strtolower($_item['key'])), 'position' => $_item['position']];
                                        }

                                    }
                                }

                            }
                        }

                    }

                }
            }

        }

        if (!is_null($fields)) {
            $response = ['status' => true, 'message' => '', 'data' => $fields];
        }

        return $response;
    }

    // TODO: need to make this method to be more scalable!
    public function map($data_source, $attributes)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $data   = null;
        $parsed = null;
        $trash_index = null;

        try {
            if ($attributes['required_fields']['status']) {
                $input_file = PHPExcel_IOFactory::identify($data_source);
                $reader = PHPExcel_IOFactory::createReader($input_file);
                $excel = $reader->load($data_source);
                $sheet = $excel->getSheet($attributes['required_fields']['data']['sheet_index']);

                $pos = $attributes['required_fields']['data']['pos'];

                if ($pos) {

                    foreach ($pos as $type => $_pos) {

                        $last_row = $sheet->getHighestRow();

                        foreach ($_pos as $store => $items) {

                            foreach ($items as $index => $_po) {
                                if ($index == 'keys') {
                                    $keys = $_po;

                                    foreach ($keys as $_keys) {
                                        $col = filter_var($_keys['position'], FILTER_SANITIZE_STRING);
                                        $row = filter_var($_keys['position'], FILTER_SANITIZE_NUMBER_INT);
                                        $col = trim($col, $row);

                                        $row++;

                                        $include  = false;

                                        $line_num = 0;

                                        for ($i = $row; $i <= $last_row; $i++) {
                                            $cell_pos = "{$col}{$i}";
                                            $cell_value_obj = $sheet->getCell($cell_pos);
                                            $cell_value     = $cell_value_obj->getValue();

                                            if ($cell_value == 'TOTAL') {
                                                $last_row = $i - 1;
                                                break;
                                            } else {
                                                if (strstr($cell_value, '=') == false) {
                                                    if (is_null($cell_value) or empty($cell_value) or $cell_value == '' or strlen($cell_value) == 0) {
                                                        $data[$attributes['document_id']][$items['po_number']][$_keys['key']][] = '';

                                                        $trash_index[$items['po_number']][$_keys['key']][] = $line_num;

                                                        $include = false;
                                                    } else {
                                                        if (!empty($cell_value)) {
                                                            $data[$attributes['document_id']][$items['po_number']][$_keys['key']][$line_num] = trim($cell_value);
                                                            $include = true;
                                                        }
                                                    }
                                                }

                                                // add UOM
                                                if ($include) {
                                                    if ($type == 'consumer') {
                                                        $data[$attributes['document_id']][$items['po_number']]['uom'][$line_num] = 'CSE';
                                                    } elseif ($type == 'otc') {
                                                        if ($_keys['key'] == 'Packing' and $cell_value == 'CASE') {
                                                            $data[$attributes['document_id']][$items['po_number']]['uom'][$line_num] = 'CSE';
                                                        } else {
                                                            $data[$attributes['document_id']][$items['po_number']]['uom'][$line_num] = 'PC';
                                                        }
                                                    }
                                                }

                                            }

                                            $line_num++;
                                        }
                                    }

                                }
                            }

                        }

                    }

                    // clean up - basis columns are item code, j&j price and quantity
                    if ($trash_index) {

                        $pos = array_keys($trash_index);

                        $tmp = [];
                        $row = [];

                        try {
                            foreach ($pos as $po) {

                                if (isset($trash_index[$po]['item_code'])) {
                                    $row = array_merge($row, $trash_index[$po]['item_code']);
                                }

                                if (isset($trash_index[$po]['jj_price'])) {
                                    $row = array_merge($row, $trash_index[$po]['jj_price']);
                                }

                                if (isset($trash_index[$po]['quantity'])) {
                                    $row = array_merge($row, $trash_index[$po]['quantity']);
                                }

                                sort($row);

                                $unique = array_unique($row);

                                $tmp[$po] = array_values($row);

                                $unique = null;
                                $row = [];
                            }

                        } catch (\Exception $e) {
                            $this->log([
                                [
                                    'action' => SYSTEM_LOGS_ACTION_ERROR,
                                    'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                                    'new_value' => json_encode(['error_msg' => $e->getMessage()]),
                                ]
                            ]);
                        }

                        foreach ($tmp as $po => $_i) {

                            foreach ($_i as $i) {

                                if (array_key_exists($i, $data[$attributes['document_id']][$po]['item_description'])) {
                                    unset($data[$attributes['document_id']][$po]['item_description'][$i]);
                                }

                                if (array_key_exists($i, $data[$attributes['document_id']][$po]['item_code'])) {
                                    unset($data[$attributes['document_id']][$po]['item_code'][$i]);
                                }

                                if (array_key_exists($i, $data[$attributes['document_id']][$po]['jj_price'])) {
                                    unset($data[$attributes['document_id']][$po]['jj_price'][$i]);
                                }

                                if (array_key_exists($i, $data[$attributes['document_id']][$po]['quantity'])) {
                                    unset($data[$attributes['document_id']][$po]['quantity'][$i]);
                                }

                                if (array_key_exists($i, $data[$attributes['document_id']][$po]['uom'])) {
                                    unset($data[$attributes['document_id']][$po]['uom'][$i]);
                                }

                            }

                            // reset array index
                            $item_description = array_values($data[$attributes['document_id']][$po]['item_description']);
                            $item_code = array_values($data[$attributes['document_id']][$po]['item_code']);
                            $jj_price = array_values($data[$attributes['document_id']][$po]['jj_price']);
                            $quantity = array_values($data[$attributes['document_id']][$po]['quantity']);
                            $uom = array_values($data[$attributes['document_id']][$po]['uom']);

                            unset($data[$attributes['document_id']][$po]['item_description']);
                            unset($data[$attributes['document_id']][$po]['item_code']);
                            unset($data[$attributes['document_id']][$po]['jj_price']);
                            unset($data[$attributes['document_id']][$po]['quantity']);
                            unset($data[$attributes['document_id']][$po]['uom']);

                            $data[$attributes['document_id']][$po]['item_description'] = $item_description;
                            $data[$attributes['document_id']][$po]['item_code'] = $item_code;
                            $data[$attributes['document_id']][$po]['jj_price'] = $jj_price;
                            $data[$attributes['document_id']][$po]['quantity'] = $quantity;
                            $data[$attributes['document_id']][$po]['uom'] = $uom;

                            if (isset($data[$attributes['document_id']][$po]['packing'])) {
                                $packing = array_values($data[$attributes['document_id']][$po]['packing']);
                                unset($data[$attributes['document_id']][$po]['packing']);

                                $data[$attributes['document_id']][$po]['packing'] = $packing;
                            }
                        }
                    }

                    foreach ($data as $document_id => $_data) {
                        foreach ($_data as $po_number => $items) {

                            $keys = array_keys($items);

                            $total_row = count($items[$keys[0]]);

                            for ($i = 0; $i <= ($total_row - 1); $i++) {

                                foreach ($keys as $key) {

                                    if (isset($items[$key][$i])) {
                                        $parsed[$document_id][$po_number][$i][$key] = $items[$key][$i];
                                    }

                                }

                            }

                        }
                    }

                }
            }

            if (!is_null($parsed)) {
                $response = $this->metaRepo->save_meta($parsed);
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();

            $this->log([
                [
                    'action' => SYSTEM_LOGS_ACTION_ERROR,
                    'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['error_msg' => $e->getMessage()]),
                ]
            ]);
        }


        return $response;
    }

    public function map_pdf($url, $xml, $document_id)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $data   = null;
        $parsed = null;
        $trash_index = null;

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($url);
            $text = $pdf->getText();
            //        save content to text
            $fileName = 'PO_' . strtotime('now') . 'txt';
            if(!file_exists(config('upload.tmp_folder').$fileName)){
                fopen(config('upload.tmp_folder').$fileName, "w+");
            }
            $file = config('upload.tmp_folder').$fileName;
            $newText = $text;
            file_put_contents($file, $newText);
            $handle = fopen($file, "r");
            $data_array = [];
            if ($handle) {
                $i = 1;
                while (($line = fgets($handle)) !== false) {
                    $data = [];
                    if (strpos($line, "GROSS")) {
                        break;
                    }

                    if($i == '1'){
                        $company_name = $line;
                    }

                    if ($i == '40') {
                        $po_number = $line;
                    }
                    if($i == '31'){
                        $loc_code = substr($line, 0, strrpos($line, '-'));
                    }
                    if($i == '43'){
                        $delivery_date = $line;
                    }

                    if ($i == '28') {
                        $vendor = substr($line, 0, strrpos($line, '-'));;
                    }

                    if ($i == '35') {
                        $approved_date = $line;
                    }

                    if ($i == '36') {
                        $cancel_date = $line;
                    }

                    if (in_array($i, $xml)) {
                        if ($i == last($xml)) {
                            $items = $this->extract_it($line);
                            $data['item_description'] = trim($items['description']);
                            $data['item_code'] = trim($items['sku']);
                            $data['jj_price'] = trim($items['jj']);
                            $data['quantity'] = trim($items['qty']);
                            $data['uom'] = trim($items['uom']);
                        }
                    }
                    if ($i > last($xml)) {
                        $items = $this->extract_it($line);
                        $data['item_description'] = trim($items['description']);
                        $data['item_code'] = trim($items['sku']);
                        $data['jj_price'] = trim($items['jj']);
                        $data['quantity'] = trim($items['qty']);
                        $data['uom'] = trim($items['uom']);
                    }

                    if (count($data) > 0) {
                        $data_array[] = $data;
                    }

                    $i++;
                }

                $parsed[$document_id] = [
                    $po_number => $data_array
                ];
                fclose($handle);

            } else {
                echo "Unable to read .txt file";
            }
            if (!is_null($parsed)) {
                $response = $this->metaRepo->save_meta($parsed);
                $response['header_details'] = [
                    //'companyname'   => trim($company_name),
                    'vendor'        => trim($vendor),
                    'approveddate'  => trim($approved_date),
                    'deliverydate'  => trim($delivery_date),
                    'canceldate'    => trim($cancel_date),
                    'loccode'       => trim($company_name) == 'ROBINSONS VENTURES CORPORATION' ? 'PH'.trim($loc_code).'V' : 'PH'.trim($loc_code).'R',
                    'ponum'         => trim($po_number)
                ];
            }
        }catch (Exception $e){
            $response['message'] = $e->getMessage();

            $this->log([
                [
                    'action' => SYSTEM_LOGS_ACTION_ERROR,
                    'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['error_msg' => $e->getMessage()]),
                ]
            ]);
        }
        return $response;

    }

    public function get_document_details($document_id, $document_meta_id)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $document = null;

        try {

            $document_obj = DB::table('documents as d')
                ->select(DB::raw('
                    b.id as batch_id,
                    d.id as document_id,
                    dd.id as document_details_id,
                    d.s3_key_name,
                    dm.id as document_meta_id,
                    case when mime_type = "application/PDF" then "PDF" else "XLS" end as file_type,
                    d.image_count,
                    d.s3_key_name,
                    d.extras as document_extras,
                    dm.extras,
                    dm.created_at,
                    dd.status
                '))
                ->join('batch as b', 'b.id', '=', 'd.batch_id')
                ->join('document_metas as dm', 'dm.document_id', '=', 'd.id')
                ->join('document_details as dd', 'dd.document_meta_id', '=', 'dm.id')
                ->where([
                    'd.id'         => $document_id,
                    'dm.id'        => $document_meta_id,
                    'd.is_active'  => 1,
                    'b.is_active'  => 1,
                    'dm.is_active' => 1
                ]);

            if ($document_obj->count()) {
                foreach ($document_obj->get() as $document) {
                    $po = json_decode($document->extras);

                    $document = [
                        'batch_id'            => $document->batch_id,
                        'document_id'         => $document->document_id,
                        'document_details_id' => $document->document_details_id,
                        'document_meta_id'    => $document->document_meta_id,
                        'po'                  => $po->file,
                        's3_key_name'         => $document->s3_key_name,
                        'extras'              => $document->document_extras,
                        'image_count'         => $document->image_count,
                        'file_type'           => $document->file_type,
                        'created_at'          => $document->created_at,
                        'status'              => $document->status
                    ];
                }
            }

            $response = ['status' => true, 'data' => $document];

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function check_if_file_exists($attributes)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];
        try{
            $filename = substr($attributes[0]->getClientOriginalName(), 0, 100);
            dd($filename);
            $document_obj = DB::table('documents as d')
                ->select(DB::raw('
                    d.*
                '))
                ->where([
                    'd.filename'         => $filename,
                ]);
            if ($document_obj->count()) {
                $response['status']  = true;
                $response['message'] = $filename.' is already existing';
            }else{
                $response['status']  = false;
            }
        }catch (Exception $e){
            $response['status'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function delete_doc_uploaded($attributes)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];
        try {
            $document_obj = DocumentsModel::where('id', $attributes['id']);

            if ($document_obj->count()) {

                $document = $document_obj->first();
                $document->is_deleted = 1;
                $document->save();

                $response = ['status' => true, 'message' => 'Document has been deleted.'];
            }
            $this->log([
                [
                    'action' => SYSTEM_LOGS_ACTION_DELETE,
                    'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['documents' => ['id' => $attributes['id']]]),
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

    public function extract_it($data)
    {
        //TODO: Crap! I need to change the variable naming here!!! For the meantime let's just leave it here --melvin
        $valueArray = explode(" ", $data);
        $firstDesc = substr($valueArray[0],9 , strlen($valueArray[0]) - 8);

        $maxIndex = max(array_keys($valueArray));
        $quantityArrayKey = $maxIndex - 1;
        $desc = "";

        for($s = 1; $s < count($valueArray) - 2; $s++){
            $desc .= " ".$valueArray[$s];
        }
        $description = $firstDesc.$desc;
        $qty = $valueArray[$quantityArrayKey];
        $items_exploded = explode('.',$valueArray[$maxIndex]);
        $length = strlen($items_exploded[2]);
        $jj = substr($items_exploded[2],2,$length - 2).'.'.substr($items_exploded[3] , 0 , 2);
        $sku = substr($items_exploded[3],2,-2);//substr($data, 0, 9);
        $uom = preg_replace('/[0-9]+/', '', substr($data, -5));

        $item['sku'] = $sku;
        $item['uom'] = $uom;
        $item['qty'] = $qty;
        $item['jj']  = $jj;
        $item['description'] =$description;
        return $item;

    }

    public function update_document_extras($document_id, $extra)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];
        try {
            $document_obj = DocumentsModel::where('id', $document_id);

            if ($document_obj->count()) {

                $document = $document_obj->first();
                $document->extras = $extra;
                $document->save();

                $response = ['status' => true, 'message' => 'Document has been deleted.'];
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            DB::rollback();
        }
        return $response;
    }
}
