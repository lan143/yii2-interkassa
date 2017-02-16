<?php
namespace lan143\interkassa;

use Yii;
use yii\base\Exception;
use yii\httpclient\Client;

class Api
{
    const URL = 'https://api.interkassa.com/v1/';

    private function request($http_method, $method, $lk_api_account_id, $data = [])
    {
        $client = new Client();
        $client->setMethod($http_method)
            ->setUrl(self::URL . $method);

        if (count($data) > 0)
            $client->setData($data);

        if ($lk_api_account_id !== null)
            $client->addHeaders(['Ik-Api-Account-Id' => $lk_api_account_id]);

        $client->addHeaders(['Authorization' => 'Basic ' . base64_encode(Yii::$app->interkassa->user_id . ':' . Yii::$app->interkassa->key)]);

        $response = $client->send();

        if ($response->isOk)
        {
            if ($response->data['code'] == 0)
                return $response->data['data'] ?? null;
            else
                throw new Exception($response->data['code'] . ': ' . $response->data['message']);
        }
        else
        {
            throw new Exception($response->statusCode);
        }
    }
}