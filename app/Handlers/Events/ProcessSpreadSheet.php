<?php namespace App\Handlers\Events;

use App\Events\EventProcessor;

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

use Excel,AWS;


class ProcessSpreadSheet
{
    protected $batchRepo;
    protected $uploadRepo;
    protected $inputRepo;


	public function __construct()
	{
		$this->batchRepo    = new BatchRepository(new BatchModel(), new ValidatorRepository());
		$this->uploadRepo   = new UploadsRepository(new DocumentsModel(), new ValidatorRepository());
        $this->inputRepo    = new InputTemplatesRepository(new InputTemplatesModel(), new ValidatorRepository());
        $this->documentRepo = new DocumentsRepository(new DocumentsModel(), new ValidatorRepository());
	}

	/**
	 * Handle the event.
	 *
	 * @param  EventProcessor  $event
	 * @return void
	 */
	public function handle(EventProcessor $event)
	{
        $batch = $this->batchRepo->get_batch($event->get_batch_id());

        if ($batch['status']) {

            $inputs = $this->inputRepo->read_input_template($batch['data'][$event->get_batch_id()]['site_id']);

            if ($inputs['status']) {

                $consumer_fields = $this->documentRepo->get_required_fields($inputs['data']['templates']['consumer']['items']);

                if (count($batch['data'])) {
                    foreach ($batch['data'] as $batch_id => $document) {
                        $download = $this->uploadRepo->download(AWS::get('s3'), $document['key'], $document['filename']);

                        if ($download['status'] and (int) $download['data']['content_length'] > 0) {

                            // update batch status
                            $this->batchRepo->update($event->get_batch_id(), ['status' => BATCH_STATUS_PROCESSING]);

                            $data = $this->documentRepo->extract(pathinfo($download['data']['filename']), [
                                'document_id'     => $document['document_id'],
                                'required_fields' => $consumer_fields['data'],
                                'templates'       => $inputs['data']['templates']
                            ]);

                            if ($data['status']) {
                                // update batch status
                                $this->batchRepo->update($event->get_batch_id(), ['status' => BATCH_STATUS_READY]);
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

        return true;
	}

}
