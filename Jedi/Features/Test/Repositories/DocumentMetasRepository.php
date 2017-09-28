<?php
namespace Jedi\Features\Documents\Repositories;

use Jedi\Features\Documents\Models\DocumentMetasModel;
use Jedi\Features\Documents\Models\DocumentDetailsModel;
use Jedi\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Model;
use Jedi\Repositories\ValidatorInterface;

use Jedi\Features\Documents\Models\MetaDetailsModel;

use DB, Carbon\Carbon;


class DocumentMetasRepository extends EloquentRepository implements DocumentMetasInterface
{
    protected $rules = [
        'key'   => 'required',
        'value' => 'required'
    ];

    protected $messages = [
        'required' => 'This :attribute field is required'
    ];


    public function __construct(Model $model, ValidatorInterface $validator)
    {
        parent::__construct($model);

        $this->validator = $validator;
    }

    public function save_meta(array $attributes)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {

            DB::beginTransaction();

            $meta_group = 1;

            foreach ($attributes as $document_id => $details) {

                foreach ($details as $po_number => $detail) {

                    $meta_obj = new DocumentMetasModel();
                    $meta_obj->document_id = $document_id;
                    $meta_obj->meta_group  = $meta_group;
                    $meta_obj->extras = json_encode(['file' => $po_number]);

                    if ($meta_obj->save()) {

                        $document_details_obj = new DocumentDetailsModel();
                        $document_details_obj->document_meta_id = $meta_obj->id;
                        $document_details_obj->document_name    = $po_number;
                        $document_details_obj->status           = DOCUMENT_STATUS_PENDING;

                        $document_details_obj->save();

                        $meta_group++;

                        foreach ($detail as $details_group => $_detail) {

                            $order = 0;

                            foreach ($_detail as $key => $value) {

                                $meta_details = new MetaDetailsModel();
                                $meta_details->document_meta_id = $meta_obj->id;
                                $meta_details->details_group    = $details_group;
                                $meta_details->meta_order       = $order;
                                $meta_details->key              = $key;
                                $meta_details->value            = $value;

                                if (!$meta_details->save()) {
                                    $this->log([
                                        [
                                            'action'    => SYSTEM_LOGS_ACTION_ERROR,
                                            'source'    => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                                            'new_value' => json_encode(['meta_details' => [
                                                'meta_id' => $meta_obj->id,
                                                'error_msg' => 'unable to save key '.$key.' with value '.$value
                                            ]])
                                        ]
                                    ]);
                                }

                                $order++;
                            }
                        }

                    } else {
                        $this->log([
                            [
                                'action'    => SYSTEM_LOGS_ACTION_ERROR,
                                'source'    => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                                'new_value' => json_encode(['meta' => ['error_msg' => 'Unable to save meta']])
                            ]
                        ]);
                    }
                }
            }

            DB::commit();

            $response = ['status' => true, 'message' => ''];

            $this->log([
                [
                    'action'    => SYSTEM_LOGS_ACTION_INSERT,
                    'source'    => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['meta' => ['document_meta_id' => $meta_obj->id]])
                ]
            ]);

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();

            DB::rollback();

            $this->log([
                [
                    'action'    => SYSTEM_LOGS_ACTION_ERROR,
                    'source'    => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => $e->getMessage()
                ]
            ]);
        }

        return $response;
    }

    public function get_document_files($batch_id)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {

            $meta_obj = DB::table($this->model->getTable() . ' as dm')
                ->select(DB::raw(
                    'dm.id,
                     dm.document_id,
                     dd.document_name,
                     dd.status,
                     concat(u.firstname, " ", u.lastname) as checked_by,
                     dd.notes,
                     dd.created_at'
                ))
                ->join('documents as d', 'd.id', '=', 'dm.document_id')
                ->join('document_details as dd', 'dd.document_meta_id', '=', 'dm.id')
                ->join('batch as b', 'b.id', '=', 'd.batch_id')
                ->leftjoin('users as u', 'u.id', '=', 'dd.checked_by')
                ->where([
                    'b.id'         => $batch_id,
                    'dm.is_active' => 1,
                    'dd.is_active' => 1
                ]);

            $meta = null;

            if ($meta_obj->count()) {
                foreach ($meta_obj->get() as $data) {
                    $meta[] = [
                        'id'            => $data->id,
                        'document_id'   => $data->document_id,
                        'document_name' => $data->document_name,
                        'status'        => $data->status,
                        'notes'         => $data->notes,
                        'checked_by'    => $data->checked_by,
                        'created_at'    => $data->created_at
                    ];
                }

                $response = ['status' => true, 'data' => $meta];
            }

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function get_document_meta_details($document_meta_id)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $meta = null;

        try  {

            $meta_obj = DB::table($this->model->getTable() . ' as dm')
                ->select(DB::raw('dm.id as document_meta_id, md.id as meta_detail_id, md.details_group, md.key, md.value'))
                ->join('meta_details as md', 'md.document_meta_id', '=', 'dm.id')
                ->where(['dm.id' => $document_meta_id, 'dm.is_active' => 1])
                ->orderBy('dm.meta_group', 'asc')
                ->orderBy('md.details_group', 'asc')
                ->orderBy('md.meta_order', 'asc');

            if ($meta_obj->count()) {
                $detail_group = 0;
                foreach ($meta_obj->get() as $data) {

                    if ($detail_group != $data->details_group) {
                        $detail_group = $data->details_group;
                    }

                    if ($detail_group == $data->details_group) {
                        $meta[$data->details_group][] = [
                            'document_meta_id' => $data->document_meta_id,
                            'meta_detail_id'   => $data->meta_detail_id,
                            'key'   => $data->key,
                            'value' => $data->value
                        ];
                    }



                }

                $response = ['status' => true, 'data' => $meta];
            }

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function validate_inputs (array $inputs)
    {
        $this->validator->with($inputs);
        $this->validator->rules($this->rules);
        $this->validator->messages($this->messages);

        if ($this->validator->passes()) {
            $status  = true;
            $errors  = null;
        } else {
            $status = false;
            $errors = $this->validator->errors()->all();
        }

        return ['status' => $status , 'errors' => $errors];
    }
}
