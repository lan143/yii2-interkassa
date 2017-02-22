<?php
namespace lan143\interkassa;

use yii\base\Action;
use yii\base\InvalidConfigException;

class BaseAction extends Action
{
    public $callback;

    protected function callback($ik_am, $ik_inv_st, $ik_pm_no)
    {
        if (!is_callable($this->callback))
            throw new InvalidConfigException('"' . get_class($this) . '::callback" should be a valid callback.');

        $response = call_user_func($this->callback, $ik_am, $ik_inv_st, $ik_pm_no);
        return $response;
    }
}