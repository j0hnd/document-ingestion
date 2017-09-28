<?php namespace Jedi\Features\Documents\Controllers;

use App\Commands\FireJediEvents;
use Jedi\Controllers\BaseController;
use Jedi\Features\Documents\Repositories\DocumentsInterface;
use Jedi\Features\Documents\Repositories\BatchInterface;

use Redirect, Request, Response, Input;


class QueueController extends BaseController
{
    protected $documentRepo;
    protected $batchRepo;


    public function __construct(DocumentsInterface $documentRepo, BatchInterface $batchRepo)
    {
        $this->documentRepo = $documentRepo;
        $this->batchRepo    = $batchRepo;

        parent::__construct();
    }

    public function index()
    {
        $this->views['title'] = 'Queue';
        $this->views['batch'] = $this->documentRepo->get_batch();

        return view('Documents::queue.index')->with($this->views);
    }

    public function filter()
    {
        if (Request::ajax()) {
            if(Request::isMethod('GET')) {
                $filters['status'] = Input::get('status');
                $this->views['batch'] = $this->documentRepo->get_batch($filters);
                $response = view('Documents::partials.queue-list')->with($this->views)->render();
            }
            return Response::json($response);
        }
    }

    public function fire_events()
    {
        $response = ['status' => false];

        if (Request::ajax()) {
            try {
                #$events = (new FireJediEvents())->queue('events');
                $events = new FireJediEvents();
                $this->dispatch($events);

                $batch = $this->documentRepo->get_batch();
                $html = view('Documents::partials.queue-list')->with(['batch' => $batch])->render();

                $response = ['status' => true, 'data' => ['html' => $html]];
            } catch (\Exception $e) {
                $this->documentRepo->log([
                    [
                        'action'    => SYSTEM_LOGS_ACTION_ERROR,
                        'source'    => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                        'new_value' => json_encode(['fire_jedi_event' => ['error_msg' => $e->getMessage()]])
                    ]
                ]);

                $response['message'] = $e->getMessage();
            }
        }

        return Response::json($response);
    }
}