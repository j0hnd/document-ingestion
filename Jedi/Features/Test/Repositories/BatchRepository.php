<?php
namespace Jedi\Features\Documents\Repositories;

use Jedi\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Model;
use Jedi\Repositories\ValidatorInterface;

use DB, Session;


class BatchRepository extends EloquentRepository implements BatchInterface
{
    protected $validator;

    protected $rules = [
        'template_name'   => 'required',
        'input_processor' => 'required',
        'destination'     => 'required',
    ];

    protected $messages = [
        'required' => 'This :attribute is a required field'
    ];


    public function __construct(Model $model, ValidatorInterface $validator)
    {
        parent::__construct($model);

        $this->validator = $validator;
    }

    public function get_batch($status = BATCH_STATUS_PENDING)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $batch = null;

        $batch_obj = DB::table($this->model->getTable() . ' as b')
            ->select(DB::raw('
                b.id as batch_id,
                d.id as document_id,
                od.output_destination_name,
                b.status,
                s.id as site_id,
                d.filename,
                d.mime_type,
                d.s3_key_name
            '))
            ->join('documents as d', 'd.batch_id', '=', 'b.id')
            ->join('sites as s', 's.id', '=', 'b.site_id')
            ->join('output_destinations as od', 'od.id', '=', 'b.output_destination_id')
            ->join('input_templates as it', 'it.site_id', '=', 's.id')
            ->where([
                'b.status'    => $status,
                'b.is_active' => 1,
                'd.is_active' => 1
            ])
            ->orderBy('b.created_at', 'desc');

        if ($batch_obj->count()) {
            foreach ($batch_obj->get() as $i => $_batch) {
                $batch[$_batch->batch_id] = [
                    'document_id' => $_batch->document_id,
                    'site_id'     => $_batch->site_id,
                    'output'      => $_batch->output_destination_name,
                    'status'      => $_batch->status,
                    'filename'    => $_batch->filename,
                    'key'         => $_batch->s3_key_name
                ];
            }

            $response = ['status' => true, 'message' => '', 'data' => $batch];
        }

        return $response;
    }

    public function save(array $inputs)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {
            if (Session::has('id')) {
                $batch_id = session('id');
                $response = ['status' => true, 'message' => 'Batch created', 'data' => ['id' => $batch_id]];
            } else {
                DB::beginTransaction();

                $this->model->upload_name = $inputs['upload_name'];
                $this->model->site_id     = $inputs['site_id'];
                $this->model->source      = BATCH_SOURCE_WEB;
                $this->model->status      = BATCH_STATUS_PENDING;
                $this->model->created_by  = $inputs['action_by'];
                $this->model->output_destination_id = $inputs['output_destination_id'];

                if ($this->model->save()) {
                    $response = ['status' => true, 'message' => 'Batch created', 'data' => ['id' => $this->model->id]];

                    session(['id' => $this->model->id]);

                    DB::commit();

                    $this->log([
                        [
                            'action'    => SYSTEM_LOGS_ACTION_INSERT,
                            'source'    => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                            'new_value' => json_encode(['batch' => ['id' => $this->model->id]]),
                            'action_by' => $inputs['action_by']
                        ]
                    ]);
                }
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();

            DB::rollback();

            $this->log([
                [
                    'action'    => SYSTEM_LOGS_ACTION_ERROR,
                    'source'    => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['error_msg' => $e->getMessage()]),
                    'action_by' => $inputs['action_by']
                ]
            ]);
        }

        return $response;
    }

    public function update_batch_status($batch_id, $updated_by, array $documents)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {

            if ($documents['status']) {
                $accept_count  = 0;
                $reject_count  = 0;
                $pending_count = 0;

                // get current batch status
                $batch = $this->find($batch_id);

                if ($batch->count()) {
                    foreach ($documents['data'] as $document) {
                        if ($document['status'] == 'Accepted') {
                            $accept_count++;
                        } elseif ($document['status'] == 'Rejected') {
                            $reject_count++;
                        } else {
                            $pending_count++;
                        }
                    }

                    if ($accept_count > 0 and $reject_count == 0 and $pending_count == 0) {
                        $data = ['status' => BATCH_STATUS_COMPLETED, 'updated' => $updated_by];
                    } elseif ($accept_count > 0 and $reject_count == 0 and $pending_count > 0) {
                        $data = ['status' => BATCH_STATUS_REVIEWED, 'updated' => $updated_by];
                    } elseif ($accept_count > 0 and $reject_count > 0) {
                        $data = ['status' => BATCH_STATUS_REVIEWED, 'updated' => $updated_by];
//                    } else {
//                        $data = ['status' => BATCH_STATUS_REJECTED, 'updated' => $updated_by];
                    }

                    if ($this->update($batch_id, $data)) {
                        $this->log([
                            [
                                'action'    => SYSTEM_LOGS_ACTION_UPDATE,
                                'source'    => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                                'old_value' => json_encode(['batch' => ['status' => $batch->status]]),
                                'new_value' => json_encode(['batch' => ['status' => $data['status']]])
                            ]
                        ]);
                    }
                } else {
                    $this->log([
                        [
                            'action'    => SYSTEM_LOGS_ACTION_ERROR,
                            'source'    => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                            'new_value' => json_encode(['error_msg' => 'batch ID '.$batch_id.' not found'])
                        ]
                    ]);
                }


            }

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();

            $this->log([
                [
                    'action'    => SYSTEM_LOGS_ACTION_ERROR,
                    'source'    => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['error_msg' => $e->getMessage()])
                ]
            ]);
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

    //for XML parsing
    public function get_documents($batch_id)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $doc = null;

        $doc_obj = DB::table('documents as d')
            ->select(DB::raw('d.id, d.batch_id'))
            ->where([
                'd.batch_id'        => $batch_id,
            ]);
        $metaDetails = null;
        $metaId = null;
        if ($doc_obj->count()) {
            foreach ($doc_obj->get() as $i => $_doc) {
//                $doc[$_doc->batch_id] = [
//                    'id' => $_doc->id,
//                ];

                $docMetas_obj = DB::table('document_metas as dm')
                    ->select(DB::raw('dm.id'))
                    ->where([
                        'dm.document_id'        => $_doc->id,
                    ]);
                foreach ($docMetas_obj->get() as $docKey => $_docMeta) {
                    $metaId[$i] = [
                        'id' => $_docMeta->id,
                    ];

                    $metaDetails_obj = DB::table('meta_details as md')
                        ->select(DB::raw('md.*'))
                        ->where([
                            'md.document_meta_id'        => $_docMeta->id,
                        ])
                        ->orderBy('md.details_group','ASC');

                    foreach ($metaDetails_obj->get() as $metaKey => $_metaDetails) {
                        $metaDetails[$_docMeta->id][$_metaDetails->details_group][$metaKey] = [
                            'id'                => $_metaDetails->id,
                            'document_meta_id'  => $_metaDetails->document_meta_id,
                            'details_group'     => $_metaDetails->details_group,
                            'meta_order'        => $_metaDetails->meta_order,
                            'key'               => $_metaDetails->key,
                            'value'             => $_metaDetails->value,
                        ];
                    }
                }
            }
            //dd($metaDetails);

            $response = ['status' => true, 'message' => '', 'data' => $metaDetails];
        }

        return $response;
    }

    public function get_meta_ids($batch_id){
        $response = ['status' => false, 'message' => 'Invalid request'];

        $doc = null;

        $doc_obj = DB::table('documents as d')
            ->select(DB::raw('d.id, d.batch_id, dm.id, dd.document_name'))
            ->join('document_metas as dm', 'd.id', '=', 'dm.document_id')
            ->join('document_details as dd','dd.document_meta_id','=','dm.id')
            ->where([
                'd.batch_id'        => $batch_id,
            ]);
        if ($doc_obj->count()) {
            foreach ($doc_obj->get() as $i => $_doc) {
                $doc[$i] = [
                    'id' => $_doc->id,
                    'document_name' => $_doc->document_name
                ];
            }
//            dd($doc);
            $response = ['status' => true, 'message' => '', 'data' => $doc];
        }

        return $response;
    }
}