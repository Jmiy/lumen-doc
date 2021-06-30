<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class UserObserver {

    /**
     * 处理 User 「新建」事件。
     *
     * @param  \App\User  $user
     * @return void
     */
    public function created(Model $model) {
        //
        dump(__METHOD__, func_get_args(), $model->toArray());
    }

    /**
     * 处理 User 「更新」 事件。
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updated(Model $model) {
        //
        dump(__METHOD__, func_get_args(), $model->toArray());
    }

    /**
     * 处理 User 「删除」 事件。
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleted(Model $model) {
        //
        dump(__METHOD__, func_get_args(), $model->toArray());
    }

    /**
     * 处理 User 「创建」 事件。
     *
     * @param  \App\User  $user
     * @return void
     */
    public function creating(Model $model) {
        //
        dump(__METHOD__, func_get_args(), $model->toArray());
    }

    /**
     * 处理 User 「获取」 事件。
     *
     * @param  \App\User  $user
     * @return void
     */
    public function retrieved(Model $model) {
        //
        dump(__METHOD__, func_get_args(), $model->toArray());
    }

    /**
     * 处理 User 「更新」 事件。  retrieved、 creating、 created、 updating、 updated、 saving、 saved、 deleting、 deleted、 restoring 和 restored
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updating(Model $model) {
        //
        dump(__METHOD__, func_get_args(), $model->toArray());
    }

    /**
     * 处理 User 「正在保存」 事件。  retrieved、 creating、 created、 updating、 updated、 saving、 saved、 deleting、 deleted、 restoring 和 restored
     *
     * @param  \App\User  $user
     * @return void
     */
    public function saving(Model $model) {
        //
        dump(__METHOD__, func_get_args(), $model->toArray());
    }

    /**
     * 处理 User 「已保存」 事件。  retrieved、 creating、 created、 updating、 updated、 saving、 saved、 deleting、 deleted、 restoring 和 restored
     *
     * @param  \App\User  $user
     * @return void
     */
    public function saved(Model $model) {
        //
        dump(__METHOD__, func_get_args(), $model->toArray());
    }

    /**
     * 处理 User 「删除中」 事件。  retrieved、 creating、 created、 updating、 updated、 saving、 saved、 deleting、 deleted、 restoring 和 restored
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleting(Model $model) {
        //
        dump(__METHOD__, func_get_args(), $model->toArray());
    }

    /**
     * 处理 User 「还原中」 事件。  retrieved、 creating、 created、 updating、 updated、 saving、 saved、 deleting、 deleted、 restoring 和 restored
     *
     * @param  \App\User  $user
     * @return void
     */
    public function restoring(Model $model) {
        //
        dump(__METHOD__, func_get_args(), $model->toArray());
    }

    /**
     * 处理 User 「已还原」 事件。  retrieved、 creating、 created、 updating、 updated、 saving、 saved、 deleting、 deleted、 restoring 和 restored
     *
     * @param  \App\User  $user
     * @return void
     */
    public function restored(Model $model) {
        //
        dump(__METHOD__, func_get_args(), $model->toArray());
    }

}
