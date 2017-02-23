<?php
namespace lan143\interkassa;

use lan143\interkassa\exceptions\HttpException;
use lan143\interkassa\exceptions\InterkassaException;
use lan143\interkassa\exceptions\WithdrawException;
use Yii;
use yii\base\InvalidConfigException;

class Component extends \yii\base\Component
{
    public $co_id;
    public $secret_key;
    public $test_key;
    public $sign_algo = 'md5';
    public $api_user_id;
    public $api_user_key;
    public $api;

    const URL = 'https://sci.interkassa.com/';

    public function init()
    {
        parent::init();

        if ($this->api_user_id === null || $this->api_user_key === null)
            throw new InvalidConfigException("Need set api_user_id and api_user_key in config file.");

        if (!in_array($this->sign_algo, ['md5', 'sha256']))
            throw new InvalidConfigException("Invalid sign algoritm.");

        $this->api = new Api();
    }

    public function generateSign(array $params)
    {
        $pairs = [];

        foreach ($params as $key => $val)
        {
            if (strpos($key, 'ik_') === 0 && $key !== 'ik_sign')
                $pairs[$key] = $val;
        }

        uksort($pairs, function($a, $b) use ($pairs) {
            $result = strcmp($a, $b);

            if ($result === 0)
                $result = strcmp($pairs[$a], $pairs[$b]);

            return $result;
        });

        array_push($pairs, YII_ENV == 'dev' ? $this->test_key
            : $this->secret_key);

        return base64_encode(hash($this->sign_algo, implode(":", $pairs), true));
    }

    public function payment(array $params)
    {
        if (!is_array($params))
            throw new \InvalidArgumentException('Params must be array');

        $params['ik_co_id'] = $this->co_id;

        return Yii::$app->response->redirect(self::URL . '?' .http_build_query($params));
    }

    /**
     * @param int $id
     * @param string $purse_name
     * @param string $payway_name
     * @param array $details
     * @param float $amount
     * @param string $calcKey Allowed: (ikPayerPrice, psPayeeAmount)
     * @param string $action Allowed: (calc, process)
     * @return mixed
     * @throws WithdrawException
     */
    public function withdraw(int $id, string $purse_name, string $payway_name, array $details, float $amount,
                             string $calcKey = 'ikPayerPrice', string $action = 'calc')
    {
        $purses = $this->api->getPurses();
        $purse = null;

        foreach ($purses as $_purse)
        {
            if ($_purse->name == $purse_name)
            {
                $purse = $_purse;
                break;
            }
        }

        if ($purse === null)
            throw new WithdrawException("Purse not found");

        if ($purse->balance < $amount)
            throw new WithdrawException("Balance in purse ({$purse->balance}) less withdraw amount ({$amount}).");

        $payways = $this->api->getOutputPayways();
        $payway = null;

        foreach ($payways as $_payway)
        {
            if ($_payway->als == $payway_name)
            {
                $payway = $_payway;
                break;
            }
        }

        if ($payway === null)
            throw new WithdrawException("Payway not found");

        try {
            $result = $this->api->createWithdraw(
                $amount,
                $payway->id,
                $details,
                $purse->id,
                $calcKey,
                $action,
                $id
            );

            if ($result->{'@resultCode'} == 0)
                return $result->transaction;
            else
                throw new WithdrawException($result->{'@resultMessage'});
        } catch (HttpException $e) {
            throw new WithdrawException('Http exception: ' . $e->getMessage());
        } catch (InterkassaException $e) {
            throw new WithdrawException('Interkassa exception: ' . $e->getMessage());
        }
    }
}