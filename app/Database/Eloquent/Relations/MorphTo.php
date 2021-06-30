<?php

namespace App\Database\Eloquent\Relations;

//use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\Builder;
//use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphTo as FrameworkMorphTo;

class MorphTo extends FrameworkMorphTo {

    /**
     * Create a new model instance by type.
     *
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModelByType($type) {
        $class = Model::getActualClassNameForMorph($type);
        $model = new $class;

        $morphToConnection = $this->getParent()->getMorphToConnection();
        $connection = data_get($morphToConnection, $type, 'default_connection');
        switch ($connection) {
            case 'default_connection':
                break;

            case 'parent':
                $model->setConnection($this->getParent()->getConnectionName());

                break;

            default:
                $model->setConnection($connection);
                break;
        }


        return $model;
    }

}
