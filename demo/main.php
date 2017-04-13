<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'api\controllers',
    'on beforeRequest'=> function ($event) {
        api\components\GlobalEvents::onBeginRequest($event);
    },
    'on afterRequest'=> function ($event) {
        api\components\GlobalEvents::onEndRequest($event);
    },
    'components' => [
        'user' => [
            'identityClass' => 'common\models\Individual',
            'enableAutoLogin' => false,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                //业务日志
                [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],//此处参数配置空，则不会输出cookie,session等内容
                    'levels' => ['info'],
                    'categories' => ['api'],
                    'logFile' => '@app/runtime/logs/info/api.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                    'rotateByCopy' => false,
                ],
                //监控接口调用耗时
                [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],//此处参数配置空，则不会输出cookie,session等内容
                    'levels' => ['info'],
                    'categories' => ['system'],
                    'logFile' => '@app/runtime/logs/info/system.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                    'rotateByCopy' => false,
                ],
                //接口监控日志－失败
                [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],//此处参数配置空，则不会输出cookie,session等内容
                    'levels' => ['error', 'info'],
                    'categories' => ['api-error'],
                    'logFile' => '@app/runtime/logs/info/api-error.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                    'rotateByCopy' => false,
                ],
                //支付日志
                [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],//此处参数配置空，则不会输出cookie,session等内容
                    'levels' => ['error', 'info'],
                    'categories' => ['pay'],
                    'logFile' => '@app/runtime/logs/info/pay.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                    'rotateByCopy' => false,
                ],
                //还款计划
                [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],//此处参数配置空，则不会输出cookie,session等内容
                    'levels' => ['error', 'info'],
                    'categories' => ['plan'],
                    'logFile' => '@app/runtime/logs/info/plan.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                    'rotateByCopy' => false,
                ],
                //通讯信息错误计数日志
                [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],//此处参数配置空，则不会输出cookie,session等内容
                    'levels' => ['info'],
                    'categories' => ['phone-error-cnt'],
                    'logFile' => '@app/runtime/logs/info/phone-error-cnt.log',
                    'maxFileSize' => 1024 * 10,
                    'maxLogFiles' => 20,
                    'rotateByCopy' => false,
                ],
                //聚信立
                [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],//此处参数配置空，则不会输出cookie,session等内容
                    'levels' => ['info'],
                    'categories' => ['juxinli'],
                    'logFile' => '@app/runtime/logs/info/juxinli.log',
                    'maxFileSize' => 1024 * 10,
                    'maxLogFiles' => 20,
                    'rotateByCopy' => false,
                ],
                //与biz通讯日志
                [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],//此处参数配置空，则不会输出cookie,session等内容
                    'levels' => ['info', 'error'],
                    'categories' => ['biz'],
                    'logFile' => '@app/runtime/logs/info/biz.log',
                    'maxFileSize' => 1024 * 10,
                    'maxLogFiles' => 20,
                    'rotateByCopy' => false,
                ],
                //机速购日志
                [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],//此处参数配置空，则不会输出cookie,session等内容
                    'levels' => ['info'],
                    'categories' => ['jisugou'],
                    'logFile' => '@app/runtime/logs/info/jisugou.log',
                    'maxFileSize' => 1024 * 10,
                    'maxLogFiles' => 20,
                    'rotateByCopy' => false,
                ],
                //消息推送日志
                [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],//此处参数配置空，则不会输出cookie,session等内容
                    'levels' => ['info'],
                    'categories' => ['push_message'],
                    'logFile' => '@app/runtime/logs/info/push_message.log',
                    'maxFileSize' => 1024 * 10,
                    'maxLogFiles' => 20,
                    'rotateByCopy' => false,
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null && !isset($response->data['code']) && ($response->format !== 'html')) {
                    $response->format = \yii\web\Response::FORMAT_JSON;
                    $response->data = [
                        'code' => 0 ,
                        'data' => $response->data
                    ];
                } elseif (isset($response->data['code'])) {
                    $response->format = \yii\web\Response::FORMAT_JSON;
                } elseif ($response->statusCode !== 200) {
                    $response->format = \yii\web\Response::FORMAT_JSON;
                    $response->data = [
                        'code' => $response->statusCode,
                        'message' => $response->statusText,
                    ];
                }
            },

        ],
    ],
    'modules' => [
        'v1' => [
            'class' => 'api\modules\v1\Module',
        ],
        'v2' => [
            'class' => 'api\modules\v2\Module',
        ],
        'app_v1' => [
            'class' => 'api\modules\app_v1\Module',
        ],
        'business_v1' => [
            'class' => 'api\modules\business_v1\Module',
        ],
        'motorcycle_v1' => [
            'class' => 'api\modules\motorcycle_v1\Module',
        ],
        'crm' => [
            'class' => 'api\modules\crm\Module',
        ],
    ],
    'params' => $params,
];
