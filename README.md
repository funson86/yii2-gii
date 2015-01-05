Yii2-gii for Chinese
========

主要针对中文和项目的优化


## default模板

### CRUD

1. 减少翻译标签，将Create Post分开成Create和Post，翻译文件的标签大大减少
2. 针对Adminlte减少h1标签
3. 针对鉴权提供can函数，默认注释
4. controller.php增加对status的支持
5. controller.php Create部分会加载数据库默认，Delete会将状态设置为STATUS_DELETE
6. search.php增加排序，按照sort_order升序, create_at降序


### Model

1. 增加beforeSave和afterSave两个函数，默认为注释掉的
2. 增加状态信息，以及$_statusLabel，并增加两个函数getArrayStatus和getStatusLabel

### Usage
1.在生成的视图index.php替换'status'

```php
[
    'attribute' => 'status',
    'format' => 'html',
    'value' => function ($model) {
            if ($model->status === $model::STATUS_ACTIVE) {
                $class = 'label-success';
            } elseif ($model->status === $model::STATUS_INACTIVE) {
                $class = 'label-warning';
            } else {
                $class = 'label-danger';
            }

            return '<span class="label ' . $class . '">' . $model->statusLabel . '</span>';
        },
    'filter' => Html::activeDropDownList(
            $searchModel,
            'status',
            $arrayStatus,
            ['class' => 'form-control', 'prompt' => Yii::t('app', 'PROMPT_STATUS')]
        )
],
```

2.在生成的视图_form.php替换如下代码

```php
<?= $form->field($model, 'status')->textInput() ?>
<?= $form->field($model, 'status')->dropDownList(\common\models\User::getArrayStatus()) ?>
```

3.在生成的视图view.php替换'status'
```php
[
    'attribute' => 'status',
    'value' => $model->statusLabel,
],
'created_at:datetime',
'updated_at:datetime',
```


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require funson86/yii2-gii "dev-master"
```

or add

```
"funson86/yii2-gii": "dev-master"
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
                    'funson86-status' => '@vendor/funson86/yii2-gii/crud/status', //name template => path to template
                ]
            ],
            'model' => [ //name generator
                'class' => 'yii\gii\generators\model\Generator', //class generator
                'templates' => [ //setting for out templates
                    'funson86' => '@vendor/funson86/yii2-gii/model/default', //name template => path to template
                    'funson86-status' => '@vendor/funson86/yii2-gii/model/status', //name template => path to template
                ]
            ],
        ],
    ];
}
```

在gii新建模板时会多出一个选项。

如果要统一输出显示的日期时间格式，修改config/main.php
```php
        'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
            'datetimeFormat' => 'yyyy-MM-dd HH:mm:ss',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'EUR',
        ],
```
