<?php namespace App\Commands;

use App\Commands\Command;

use App\Events\EventProcessor;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use Jedi\Features\Documents\Models\BatchModel;
use Jedi\Features\Documents\Models\DocumentsModel;
use Jedi\Features\Sites\Models\InputTemplatesModel;

use Jedi\Features\Documents\Repositories\BatchRepository;
use Jedi\Features\Documents\Repositories\UploadsRepository;
use Jedi\Features\Documents\Repositories\DocumentsRepository;
use Jedi\Features\Sites\Repositories\InputTemplatesRepository;
use Jedi\Repositories\ValidatorRepository;

use AWS, Excel;

class FireJediEvents extends Command implements SelfHandling, ShouldBeQueued
{
    protected $batchRepo;
    protected $inputRepo;


	public function __construct()
	{
        $this->batchRepo    = new BatchRepository(new BatchModel(), new ValidatorRepository());
        $this->uploadRepo   = new UploadsRepository(new DocumentsModel(), new ValidatorRepository());
        $this->inputRepo    = new InputTemplatesRepository(new InputTemplatesModel(), new ValidatorRepository());
        $this->documentRepo = new DocumentsRepository(new DocumentsModel(), new ValidatorRepository());
	}

	public function handle()
	{
        try {
            $batch = $this->batchRepo->get_batch(BATCH_STATUS_PENDING);

            if ($batch['status']) {
                foreach ($batch['data'] as $batch_id => $_batch) {
                    $inputs = $this->inputRepo->read_input_template($batch['data'][$batch_id]['site_id']);

                    if ($inputs['status']) {

                        if (count($batch['data'])) {
                            foreach ($batch['data'] as $batch_id => $document) {
                                $download = $this->uploadRepo->download(AWS::get('s3'), $document['key'], $document['filename']);

                                if ($download['status'] and (int) $download['data']['content_length'] > 0) {

                                    // update batch status
                                    $this->batchRepo->update($batch_id, ['status' => BATCH_STATUS_PROCESSING]);

                                    $input_file_name = pathinfo($download['data']['filename']);

                                    if (isset($inputs['data']['file_type'])) {
                                        if ($inputs['data']['file_type'] == "pdf") {
                                            $reference['url']       = $this->uploadRepo->getUrl(AWS::get('s3'), $batch['data'][$batch_id]['key']);
                                            $reference['templates'] = $inputs['data']['templates'];
                                        }

                                    } else {
                                        $reference = $this->documentRepo->get_required_fields($input_file_name, $inputs['data']);
                                    }



                                    $data = $this->documentRepo->extract($input_file_name, [
                                        'document_id'     => $document['document_id'],
                                        'required_fields' => $reference
                                    ]);

                                    if ($data['status']) {
                                        // update batch status
                                        $this->batchRepo->update($batch_id, ['status' => BATCH_STATUS_READY]);

                                        if (isset($inputs['data']['file_type'])) {
                                            if ($inputs['data']['file_type'] == "pdf") {
                                                $company = ['companyname' => trim($inputs['data']['company'])];
                                                $header_details = array_merge($company,$data['header_details']);
                                                $extras = json_encode($header_details);
                                                $this->documentRepo->update_document_extras($document['document_id'], $extras);
                                            }
                                        }

                                    }
                                }
                            }

                            // remove downloaded file
                            unlink($download['data']['filename']);
                        }

                    } else {
                        $this->batchRepo->log([
                            [
                                'action' => SYSTEM_LOGS_ACTION_ERROR,
                                'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                                'new_value' => json_encode(['event_processor' => [
                                    'error_msg' => 'Error reading processor'
                                ]])
                            ]
                        ]);

                        throw new \Exception('Error reading processor');
                    }


                }
            }
        } catch (Exception $e) {
            $this->batchRepo->log([
                [
                    'action'    => SYSTEM_LOGS_ACTION_ERROR,
                    'source'    => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['fire_jedi_event' => ['error_msg' => $e->getMessage()]])
                ]
            ]);
        }

	}

}
