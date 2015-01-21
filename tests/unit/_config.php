<?php

return yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../../../config/web.php'),
    [
        'components' => [
            'db' => [
                'dsn' => 'mysql:host=localhost;dbname=yii2_basic_unit',
            ],
            'mailer' => [
                'useFileTransport' => true,
            ],
            'urlManager' => [
                'showScriptName' => true,
            ],
        ],
    ]
);