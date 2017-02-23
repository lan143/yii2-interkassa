<?php
namespace lan143\interkassa\tests;

use lan143\interkassa\Api;
use lan143\interkassa\exceptions\HttpException;
use Yii;

class ApiTest extends TestCase
{
    public function testGetCurrencies()
    {
        $api = new Api();
        $currencies = $api->request('GET', 'currency');
        $this->assertTrue(is_array($currencies));
    }

    public function testIncorrectRequest()
    {
        $this->expectException(HttpException::class);
        $api = new Api();
        $api->request('GET', 'incorrect-request');
    }

    public function testIncorrectParams()
    {
        $this->expectException(HttpException::class);
        $api = new Api();
        $api->request('GET', 'account');
    }
}