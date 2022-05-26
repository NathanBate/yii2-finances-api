<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'api-web',
    'name' => "API",
    'version' => '0.1',
    'basePath' => dirname(__DIR__),
    'class' => baseapi\web\Application::class,
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\controllers\web',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'class' => baseapi\web\Request::class,
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'urlManager' => [
            'class' => yii\web\UrlManager::class,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'ruleConfig' => [
                'class' => yii\web\UrlRule::class
            ],
            'rules' => [
                'transactions/<year:\d+>/<month:\d+>/<account:\d+>' => "transaction/index/",
                'transaction/get-scheduled-transactions/<account:\d+>/<statement:\d+>' => 'transaction/get-scheduled-transactions/',
                'transaction/get-recipients-and-payers/<id:\d+>/<year:\d+>/<month:\d+>' => 'transaction/get-recipients-and-payers/',
                '<controller>/create' => '<controller>/create',
                '<controller>/<action>/<id:\d+>' => '<controller>/<action>',
                '<controller>/<action>/<accountid:\d+>/<statementid:\d+>' => '<controller>/<action>',
                '<controller>/<action>/<id:\d+>/<userid:\d+>' => '<controller>/<action>',
                '<controller>/<action>/<id:\d+>/<year:\d+>/<month:\d+>' => '<controller>/<action>',
                '<controller>s' => '<controller>/index',
            ]
        ],
        'user' => [
            'identityClass' => app\models\UserModel::class,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
    'defaultRoute' => 'api/index'
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => yii\debug\Module::class
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];

}

return $config;
