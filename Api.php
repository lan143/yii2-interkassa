<?php
namespace lan143\interkassa;

use lan143\interkassa\exceptions\HttpException;
use lan143\interkassa\exceptions\InterkassaException;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;

class Api
{
    const URL = 'https://api.interkassa.com/v1/';

    public function getAccounts()
    {
        return $this->request('GET', 'account');
    }

    public function getCheckout()
    {
        return self::request('GET', 'checkout', $this->getLkApiAccountId());
    }

    public function getPurses()
    {
        return $this->request('GET', 'purse', $this->getLkApiAccountId());
    }

    public function getCoInvoices()
    {
        return $this->request('GET', 'co-invoice', $this->getLkApiAccountId());
    }

    public function getWithdraws()
    {
        return $this->request('GET', 'withdraw/', $this->getLkApiAccountId());
    }

    public function getWithdraw($id)
    {
        return $this->request('GET', 'withdraw/'.$id, $this->getLkApiAccountId());
    }

    public function createWithdraw($amount, $paywayId, $details, $purseId, $calcKey, $action, $paymentNo)
    {
        return $this->request('POST', 'withdraw', $this->getLkApiAccountId(), [
            'amount' => $amount,
            'paywayId' => $paywayId,
            'details' => $details,
            'purseId' => $purseId,
            'calcKey' => $calcKey,
            'action' => $action,
            'paymentNo' => $paymentNo,
        ]);
    }

    public function getCurrencies()
    {
        $cache = Yii::$app->cache;
        if (($data = $cache->get('interkassa.currency')) !== null)
            return $data;
        else
        {
            $response = $this->request('GET', 'currency');
            $cache->set('interkassa.currency', $response, 86400);
            return $response;
        }
    }

    public function getInputPayways()
    {
        $cache = Yii::$app->cache;
        if (($data = $cache->get('interkassa.input_payways')) !== null)
            return $data;
        else
        {
            $response = $this->request('GET', 'paysystem-input-payway');
            $cache->set('interkassa.input_payways', $response, 86400);
            return $response;
        }
    }

    public function getOutputPayways()
    {
        $cache = Yii::$app->cache;
        if (($data = $cache->get('interkassa.output_payways')) !== null)
            return $data;
        else
        {
            $response = $this->request('GET', 'paysystem-output-payway', null);
            $cache->set('interkassa.output_payways', $response, 86400);
            return $response;
        }
    }

    public function request($http_method, $method, $lk_api_account_id = null, $data = [])
    {
        if (Yii::$app->interkassa === null)
            throw new InvalidConfigException("Interkassa component not inited.");

        $client = new Client();
        $request = $client->createRequest()
            ->setMethod($http_method)
            ->setUrl(self::URL . $method);

        if (count($data) > 0)
            $request->setData($data);

        if ($lk_api_account_id !== null)
            $request->addHeaders(['Ik-Api-Account-Id' => $lk_api_account_id]);

        $request->addHeaders(['Authorization' => 'Basic ' . base64_encode(Yii::$app->interkassa->api_user_id . ':' . Yii::$app->interkassa->api_user_key)]);

        $response = $request->send();

        if ($response->isOk)
        {
            if ($response->data['code'] == 0)
                return $response->data['data'] ?? null;
            else
                throw new InterkassaException($response->data['code'] . ': ' . $response->data['message']);
        }
        else
        {
            throw new HttpException($response->statusCode);
        }
    }

    private function getLkApiAccountId() : string
    {
        $cache = Yii::$app->cache;
        if (($lk_api_account_id = $cache->get('interkassa.lk_api_account_id')) !== null)
            return $lk_api_account_id;
        else
        {
            $accounts_info = $this->getAccounts();
            $lk_api_account_id = null;

            foreach ($accounts_info as $account_info)
            {
                if ($account_info->tp == 'b')
                {
                    $lk_api_account_id = $account_info->_id;
                    break;
                }
            }

            if ($lk_api_account_id === null)
                throw new Exception("Business id not found");

            $cache->set('interkassa.lk_api_account_id', $lk_api_account_id, 86400);
            return $lk_api_account_id;
        }
    }
}