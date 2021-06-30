<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class BaseEvent extends Event {

    use SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     * BaseEvent constructor.
     * @param $data
     * @return void
     */
    public function __construct($data) {
        //
        $this->data = $data;
    }

}
