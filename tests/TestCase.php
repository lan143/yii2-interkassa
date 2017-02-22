<?php
namespace lan143\interkassa\tests;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the base class for all yii framework unit tests.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();
    }

    protected function tearDown()
    {
        $this->destroyApplication();
    }

    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
                'session' => [
                    'class' => 'yii\web\DbSession',
                ],
                'interkassa' => [
                    'class' => 'lan143\interkassa\Component',
                    'co_id' => 'kassa123456',
                    'secret_key' => 'secret123456',
                    'test_key' => 'testsecret123456',
                    'sign_algo' => 'md5',
                    'api_user_id' => '',
                    'api_user_key' => ''
                ],
            ],
        ], $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
    }
}