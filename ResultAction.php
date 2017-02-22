<?php
namespace lan143\interkassa;

use Yii;
use yii\web\BadRequestHttpException;

class ResultAction extends BaseAction
{
    public function run()
    {
        $ik_co_id = Yii::$app->request->post('ik_co_id');
        $ik_pm_no = Yii::$app->request->post('ik_pm_no');
        $ik_am = Yii::$app->request->post('ik_am');
        $ik_inv_st = Yii::$app->request->post('ik_inv_st');
        $ik_sign = Yii::$app->request->post('ik_sign');

        if ($ik_co_id == Yii::$app->interkassa->co_id
            && $ik_sign == Yii::$app->interkassa->generateSign(Yii::$app->request->bodyParams))
            return $this->callback($ik_am, $ik_inv_st, $ik_pm_no);
        else
            throw new BadRequestHttpException;
    }
}