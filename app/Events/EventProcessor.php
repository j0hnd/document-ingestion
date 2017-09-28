<?php namespace App\Events;

use App\Events\Event;

use Illuminate\Queue\SerializesModels;


class EventProcessor extends Event
{
	use SerializesModels;

    protected $batch_id;


	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($batch_id)
	{
		$this->set_batch_id($batch_id);
	}

    public function get_batch_id()
    {
        return $this->batch_id;
    }

    public function set_batch_id($batch_id)
    {
        $this->batch_id = $batch_id;
    }
}
