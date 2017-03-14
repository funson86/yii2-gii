Yii2-gii for Chinese
========

主要针对中文和项目的优化


## default模板

### Model

1. 在model.php头部增加$labelList = ['type', 'kind']; 如果其他需要生成getXXXLabels函数会自动生成，适用于需要定义常量的字段。
2. 增加beforeSave和afterSave两个函数，默认为注释掉的
3. 增加model保存时自动附加时间和操作用户TimestampBehavior::className(),BlameableBehavior::className(),
4. 增加getImportFields和getExportFields两个函数，用于指定导入导出csv对应的类型，类型暂时支持relation/enum/int/text。‘_id’默认为relation类型，自动转换为该类型的name；enum对应$labelList中所指定，自动对应getXxxLabels中标签；int对应字段类型为integer或boolean；其他为text类型。

### CRUD

#### Controller
1. 针对鉴权提供can函数
2. 对actionDelete增加软删除，即将状态设置为STATUS_DELETE状态
3. search.php增加排序，默认按照create_at降序，可启用按照sort_order升序, create_at降序
4. 增加import和export函数，对应controller-id/import和controller-id/export路由
5. 导入的模板需要放在backend/web/template/table.csv文件

#### Views
1. 在index.php _form.php view.php文件的头部有$labelList = ['type', 'kind']; 在index中会使用标签以及生成下拉列表筛选， 表单中增加下拉列表  view中会转换成对应的标签
2. index、view中所有的‘_at’结尾的字段转变成时间字符串，所有的‘_by’会显示成user表中的username，且以这两个不会在表单中出现，自动使用model中的
3. 所有以‘_id’结尾的字段，都会对前面的字段进行处理成相关model中的name字段，对于user表则显示为username字段。index、view中显示name内容，form表中显示成id=>name下拉列表
4. 如果有‘status’字段，都会解析成common\models\Status中的标签。index、view中显示标签，form表变成下拉列表
5. 如果以‘_status’结尾的字段，则会解析成当前model中对应的getXxxStatusLabels函数中的标签，form表变成下拉列表


### 数据表设计

1. 除parent_id外，所有的以‘_id’结尾的字段需要定义外键
2. 所有的状态使用‘status’或者以‘_status’结尾
3. 最好加上created_at，updated_at，created_by，updated_by
4. 需要常量的字段在model.php index.php _form.php view.php中添加到$labelList中

所有的表格最好以一下方式结尾
```php
  `sort_order` int(11) NOT NULL DEFAULT '50',
  `status` int(11) NOT NULL DEFAULT '1',
  `created_at` int(11) NOT NULL DEFAULT '1',
  `updated_at` int(11) NOT NULL DEFAULT '1',
  `created_by` int(11) NOT NULL DEFAULT '1',
  `updated_by` int(11) NOT NULL DEFAULT '1',
```

一个常用的表：
```php
CREATE TABLE `prefix_test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '1',
  `sort_order` int(11) NOT NULL DEFAULT '50',
  `status` int(11) NOT NULL DEFAULT '1',
  `created_at` int(11) NOT NULL DEFAULT '1',
  `updated_at` int(11) NOT NULL DEFAULT '1',
  `created_by` int(11) NOT NULL DEFAULT '1',
  `updated_by` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `product_id` (`product_id`),
  KEY `created_at` (`created_at`),
  KEY `FK_test_plan_id` (`plan_id`),
  CONSTRAINT `FK_test_product_id` FOREIGN KEY (`product_id`) REFERENCES `prefix_product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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

一些小技巧参考  [tips](tips.md)
