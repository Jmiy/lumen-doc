<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class ExampleEvent extends Event {

    use SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Model $model) {
        //
        $this->data = $model->toArray();
    }

}
