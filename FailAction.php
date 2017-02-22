<?php
namespace lan143\interkassa;

use Yii;
use yii\web\BadRequestHttpException;

class FailAction extends BaseAction
{
    public function run()
    {
        $ik_pm_no = Yii::$app->request->post('ik_pm_no');
        $ik_am = Yii::$app->request->post('ik_am');
        $ik_inv_st = Yii::$app->request->post('ik_inv_st');

        if (!$ik_pm_no && !$ik_am && !$ik_inv_st)
            throw new BadRequestHttpException;
        else
            return $this->callback($ik_am, $ik_inv_st, $ik_pm_no);
    }
}