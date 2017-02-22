Yii2 Interkassa
===============
Extension for integration Interkassa in yii2 project. WIP.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist lan143/yii2-interkassa "*"
```

or add

```
"lan143/yii2-interkassa": "*"
```

to the require section of your `composer.json` file.

Update config file config/web.php
```php
return [
    'components' => [
        'interkassa' => [
            'class' => 'lan143\interkassa\Component',
            'co_id' => '', // Cashbox identifier
            'secret_key' => '', // Cashbox secret key
            'test_key' => '', // Cashbox test secret key
            'sign_algo' => 'md5', // Sign algoritm. Allow: md5, sha1
            'api_user_id' => '', // Api user id
            'api_user_key' => '' // Api user secret key
        ],
    ],
]
```


Usage
-----
Example payment:
```php
class InterkassaController extends Controller
{
    public function actions() {
        return [
            'result' => [
                'class' => 'lan143\interkassa\ResultAction',
                'callback' => [$this, 'resultCallback'],
            ],
            'success' => [
                'class' => 'lan143\interkassa\SuccessAction',
                'callback' => [$this, 'successCallback'],
            ],
            'fail' => [
                'class' => 'lan143\interkassa\FailAction',
                'callback' => [$this, 'failCallback'],
            ],
        ];
    }

    public function actionInvoice()
    {
        $model = new Invoice();

        if ($model->load(Yii::$app->request) && $model->save())
        {
            $params = [
                'ik_pm_no' => $model->id,
                'ik_am' => $model->ammount,
                'ik_desc' => 'Site payment',
            ];

            return Yii::$app->interkassa->payment($params);
        }

        return $this->render('invoice', compact($model));
    }

    public function successCallback($ik_am, $ik_inv_st, $ik_pm_no)
    {
        return $this->render('success');
    }

    public function failCallback($ik_am, $ik_inv_st, $ik_pm_no)
    {
        return $this->render('fail');
    }

    public function resultCallback($ik_am, $ik_inv_st, $ik_pm_no)
    {

        switch ($ik_inv_st)
        {
            case 'new':
                $this->loadModel($ik_pm_no)->updateAttributes(['status' => Invoice::STATUS_NEW]);
                break;
            case 'waitAccept':
                $this->loadModel($ik_pm_no)->updateAttributes(['status' => Invoice::STATUS_PENDING]);
                break;
            case 'process':
                $this->loadModel($ik_pm_no)->updateAttributes(['status' => Invoice::STATUS_PROCESS]);
                break;
            case 'success':
                $this->loadModel($ik_pm_no)->updateAttributes(['status' => Invoice::STATUS_SUCCESS]);
                break;
            case 'canceled':
                $this->loadModel($ik_pm_no)->updateAttributes(['status' => Invoice::STATUS_CANCELED]);
                break;
            case 'fail':
                $this->loadModel($ik_pm_no)->updateAttributes(['status' => Invoice::STATUS_FAIL])
                break;
        }
    }

    protected function loadModel($id)
    {
        $model = Invoice::findOne($id);

        if ($model === null)
            throw new BadRequestHttpException;

        return $model;
    }
}
```

Example withdraw:
```php
class Withdraw
{
    protected $purse_name = 'My Purse Name';

    public function process($withdraw)
    {
        $api = new \lan143\interkassa\Api;

        $purses = $api->getPurses();
        $purse = null;

        foreach ($purses as $_purse)
        {
            if ($_purse->name == $this->purse_name)
            {
                $purse = $_purse;
                break;
            }
        }

        if ($purse === null)
            throw new \Exception("Purse not found");

        if ($purse->balance < $withdraw->amount)
            throw new \Exception("Balance in purse ({$purse->balance}) less withdraw amount ({$withdraw->amount}).");

        $payways = $api->getOutputPayways();
        $payway = null;

        foreach ($payways as $_payway)
        {
            if ($_payway->als == $withdraw->payway_name) // for example: webmoney_webmoney_transfer_wmz
            {
                $payway = $_payway;
                break;
            }
        }

        if ($payway === null)
            throw new \Exception("Payway not found");

        $details = [
            'purse' => $withdraw->purse // for example: Z1234567890
        ];

        $result = self::createWithdraw(
            $withdraw->amount,
            $payway->id,
            $details,
            $purse->id,
            'psPayeeAmount',
            'process',
            $id
        );

        if ($result->{'@resultCode'} == 0)
        {
            return $result->transaction;
        }
        else
            throw new \Exception($result->{'@resultMessage'});
    }
}