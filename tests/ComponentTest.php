<?php
namespace lan143\interkassa\tests;

use Yii;

class ComponentTest extends TestCase
{
    public function testSign()
    {
        $params = [
            'ik_co_id' => 'kassa123456',
            'ik_pm_no' => '123',
            'ik_am' => '100',
            'ik_inv_st' => 'fail',
            'ik_sign' => 'HPx/bS7CUjIg24e8naX4aw==',
        ];

        $this->assertEquals($params['ik_sign'], Yii::$app->interkassa->generateSign($params));
    }
}