<?php
namespace lan143\interkassa\exceptions;

use yii\base\Exception;

class HttpException extends Exception
{
    public function getName()
    {
        return 'Invalid http response';
    }
}