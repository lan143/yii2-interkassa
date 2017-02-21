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