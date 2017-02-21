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
    }
}