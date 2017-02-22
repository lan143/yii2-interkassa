<?php
namespace lan143\interkassa;

use yii\base\InvalidConfigException;

class Component extends \yii\base\Component
{
    public $co_id;
    public $secret_key;
    public $test_key;
    public $sign_algo = 'md5';
    public $api_user_id;
    public $api_user_key;

    public function init()
    {
        parent::init();

        if ($this->api_user_id === null || $this->api_user_key === null)
            throw new InvalidConfigException("Need set api_user_id and api_user_key in config file.");

        if (!in_array($this->sign_algo, ['md5', 'sha256']))
            throw new InvalidConfigException("Invalid sign algoritm.");
    }

    public function generateSign($params)
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
}