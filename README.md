Yii2-gii for Chinese
========

主要针对中文的优化

CRUD

1. 减少翻译标签，将Create Post分开成Create和Post，翻译文件的标签大大减少
2. 针对Adminlte减少h1标签
3. 针对鉴权提供can函数，默认注释


Model

1. 增加beforeSave和afterSave两个函数，默认修改create_time和update_time

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist funson86/yii2-gii "*"
```

or add

```
"funson86/yii2-gii": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

在main-local.php中配置：

```php
if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', '192.168.178.20'],
        'generators' => [ //here
            'crud' => [ //name generator
                'class' => 'yii\gii\generators\crud\Generator', //class generator
                'templates' => [ //setting for out templates
                    'funson86' => '@vendor/funson86/yii2-gii/crud/default', //name template => path to template
                ]
            ],
            'model' => [ //name generator
                'class' => 'yii\gii\generators\model\Generator', //class generator
                'templates' => [ //setting for out templates
                    'funson86' => '@vendor/funson86/yii2-gii/model/default', //name template => path to template
                ]
            ],
        ],
    ];
}
```

在gii新建模板时会多出一个选项。
