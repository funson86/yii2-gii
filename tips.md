Yii2 tips
=======

### backend开发注意事项
1. 在gii生成的基础上加上后台权限访问控制
2. 


### 在view文件中createUrl 和Html链接
```
<?php echo \Yii::$app->getUrlManager()->createUrl(['catalog/create', 'parent_id'=>$item['id']]); ?
Html::a(Html::encode($tag), ['blog/index','tag'=>$tag]);
```

### 在view文件_form.php中droplist下拉菜单，单选框
http://www.yiiframework.com/wiki/653/displaying-sorting-and-filtering-model-relations-on-a-gridview/
```
use yii\helpers\ArrayHelper;
use backend\models\Standard;
<?= $form->field($model, 'center_id')->dropDownList(ArrayHelper::map(Course::find()->asArray()->all(),'id','name'), ['prompt'=> Yii::t('app', 'Please Select')?>
<?= Html::activeRadioList($model, 'customer_sex', \common\models\Car::getArrayCustomerSex(), [
    'class' => 'wj_radio',
    'item' => function ($index, $label, $name, $checked, $value){
        return '<em>' . Html::radio($name, $checked, ['value' => $value]) . '<strong>' . $label . '</strong></em>';
    },
]); ?>
```
### 在view文件index.php中显示状态和下拉选择
```
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
                        ['class' => 'form-control', 'prompt' => Yii::t('user', 'BACKEND_PROMPT_STATUS')]
                    )
            ],
```
在view文件view.php中
```
            [
                'attribute' => 'parent_id',
                'value' => $model->parent_id ? $model->parent->title : Yii::t('blog', 'Root Catalog'),
            ],
            'role',
            [
                'attribute' => 'status',
                'value' => $model->statusLabel,
            ],
            [
                'attribute' => 'created_at',
                'value' => date('Y-m-d H:i:s', $model->created_at),
            ],
            [
                'attribute' => 'updated_at',
                'value' => date('Y-m-d H:i:s', $model->updated_at),
            ],
```


### 分页显示
```
namespace app\controllers;

use yii\web\Controller;
use yii\data\Pagination;
use app\models\Country;

class CountryController extends Controller
{
    public function actionIndex()
    {
        $query = Country::find();

        $pagination = new Pagination([
            'defaultPageSize' => Yii::$app->params['defaultPageSize'],
            'totalCount' => $query->count(),
        ]);

        $countries = $query->orderBy('name')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'countries' => $countries,
            'pagination' => $pagination,
        ]);
    }
}
$dataProvider = new ActiveDataProvider([
    'query' => User::find(),
    'pagination' => array('pageSize' => 50),
]);
echo \yii\widgets\LinkPager::widget([
    'pagination'=>$dataProvider->pagination,
]);

在view文件中
use yii\helpers\Html;
use yii\widgets\LinkPager;
?>
<h1>Countries</h1>
<ul>
<?php foreach ($countries as $country): ?>
    <li>
        <?= Html::encode("{$country->name} ({$country->code})") ?>:
        <?= $country->population ?>
    </li>
<?php endforeach; ?>
</ul>

<?= LinkPager::widget(['pagination' => $pagination]) ?>
```

### 权限控制
```
use yii\filters\AccessControl;

public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::className(),
            'only' => ['create', 'update'],
            'rules' => [
                // allow authenticated users
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
                // everything else is denied by default
            ],
        ],
    ];
}
```
### 后台的标签和输入框在一行显示
https://github.com/yiisoft/yii2/blob/master/apps/basic/views/site/login.php
```
    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>
```

https://github.com/kartik-v/yii2-widgets 第三方的widget，不过使用的google相关文件，国内使用会有点问题

### Yii2核心组件
```
    public function coreComponents()
    {
        return [
            'log' => ['class' => 'yii\log\Dispatcher'],
            'view' => ['class' => 'yii\web\View'],
            'formatter' => ['class' => 'yii\i18n\Formatter'],
            'i18n' => ['class' => 'yii\i18n\I18N'],
            'mailer' => ['class' => 'yii\swiftmailer\Mailer'],
            'urlManager' => ['class' => 'yii\web\UrlManager'],
            'assetManager' => ['class' => 'yii\web\AssetManager'],
            'security' => ['class' => 'yii\base\Security'],
        ];
    }
```

### Yii2的model beforeSave 和afterSave
```
    /**
     * Before save.
     * create_time update_time
     */
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($this->isNewRecord)
            {
                $this->create_time = new Expression('NOW()');
                $this->update_time = $this->create_time;
            }
            else
            {
                $this->update_time = new Expression('NOW()');
            }
            return true;
        }
        else
            return false;
    }
    /**
     * After save.
     *
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        // add your code here
        Tag::model()->updateFrequency($this->_oldTags, $this->tags);
    }
```

### 后台主界面排序和筛选，搜索http://www.yiiframework.com/wiki/679/filter-sort-by-summary-data-in-gridview-yii-2-0/

### index界面左边的序号变成多选框，['class' => 'yii\grid\SerialColumn'],改成 ['class' => 'yii\grid\CheckboxColumn'],

### form field可以拆分成3个，当使用隐藏字段时候，label不显示，可以只使用activeHiddenInput。
```
<?=Html::activeLabel($user,'email');?>
<?=Html::activeTextInput($user,'email', ['class' => 'user', 'placeholder' => '请输入帐号/手机号']);?>
<?=Html::error($user,'email');?>
```

### 简化Gridview中的时间显示，首先设置在应用中设置formatter的格式，然后只需要简单的在create_time修改为create_time:date即可。
```
        'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
            'datetimeFormat' => 'yyyy-MM-dd HH:mm:ss',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'EUR',
        ],
```

### 如果服务器不能显示Yii debugger toolbar，一般情况是服务器的debugger IP没有设置为*，在main-local.php中修改
```
if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug']['class'] = 'yii\debug\Module';
    $config['modules']['debug']['allowedIPs'] = ['*'];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii']['class'] = 'yii\gii\Module';
    $config['modules']['gii']['allowedIPs'] = ['*'];
}
```

### 在后台开启数据库的表结构缓存，只需要在common\config\main-local.php文件中增加enableSchemaCache 和 schemaCache
```
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=after',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'schemaCache' => 'cache',
        ],
    ]
```

### 前端不需要bootstrap.css文件，在main.php文件中添加：
```
        'assetManager' => [
            'bundles' => [
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [],
                ],
                'yii\web\JqueryAsset' => [
                   'js'=>[]
                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js'=>[]
                ],
            ],
        ],
```

### 前端不同的页面的js或css文件不同，则需要注册对应的文件，但是需要在Asset后面，则定义'depends'，js可指定位置
```
$this->registerCssFile('@web/web/css/login.css', ['depends' => \frontend\assets\WebAsset::className()]);
$this->registerJsFile('@web/web/js/check.js', ['depends' => \frontend\assets\WebAsset::className(), 'position' => \yii\web\View::POS_HEAD]);
$this->registerJs(
    '$("document").ready(function(){ alert("hi"); });'
);
```

### 如何去掉前端有冲突的表单验证代码，增加enableClientScript，此时如果开启csrf验证的话ajax请求会有问题
```
<?php $form = ActiveForm::begin([
    'enableClientScript' => false,
    'enableClientValidation' => false,
    'enableAjaxValidation' => false,
]); ?>
```

### 创建一个互相依赖的下拉菜单
http://www.yiiframework.com/wiki/723/creating-a-dependent-dropdown-from-scratch-in-yii2/

### 配置Yii2全局参数和读取方式
在params.php文件中定义全局参数，如分页数量，使用如下代码调用
Yii::$app->params['defaultPageSize']


### 使用ActiveDataProvider自适应排序，在URL中使用sort=-name降序排序，sort=name升序排序
```
        $dataProvider = new ActiveDataProvider([
            'query' => Car::find()->where(['user_id' => Yii::$app->user->identity->id]),
            'pagination' => array('defaultPageSize' => Yii::$app->params['defaultPageSize']),
            'sort' => ['defaultOrder' => ['sort_order' => SORT_ASC, 'create_time' => SORT_DESC]],
        ]);

        return $this->render('index', [
            'cars' => $dataProvider->getModels(),
            'pagination' => $dataProvider->pagination,
        ]);
```

在getModels()函数中会调用到 \yiisoft\yii2\data\ActiveDataProvider::prepareModels，该函数会自动加载URL中的sort参数。


### Yii 预定义别名
默认预定义别名一共有12个，其中路径别名11个，URL别名只有 @web 1个：
```
@yii 表示Yii框架所在的目录，也是 yii\BaseYii 类文件所在的位置；
@app 表示正在运行的应用的根目录，一般是 digpage.com/frontend ；
@vendor 表示Composer第三方库所在目录，一般是 @app/vendor 或 @app/../vendor ；
@bower 表示Bower第三方库所在目录，一般是 @vendor/bower ；
@npm 表示NPM第三方库所在目录，一般是 @vendor/npm ；
@runtime 表示正在运行的应用的运行时用于存放运行时文件的目录，一般是 @app/runtime ；
@webroot 表示正在运行的应用的入口文件 index.php 所在的目录，一般是 @app/web；
@web URL别名，表示当前应用的根URL，主要用于前端；
@common 表示通用文件夹；
@frontend 表示前台应用所在的文件夹；
@backend 表示后台应用所在的文件夹；
@console 表示命令行应用所在的文件夹；
其他使用Composer安装的Yii扩展注册的二级别名。
```

### Yii migrate 升级 降级 定义路径
在命令行下执行：
```
yii migrate 会列出@console/migrations的文件并提示升级
yii migrate/down 会根据migration表按顺序降级
yii migrate --migrationPath=@console/migrations/rbac  指定升级console/migrations/rbac目录下的文件
yii migrate/down --migrationPath=@console/migrations/rbac  指定降级要执行的文件在在console/migrations/rbac
```

### Yii2 事件
首先在frontend\components新建foo.php文件
```php
namespace frontend\components;
use yii\base\Component;
use yii\base\Event;
class Foo extends Component
{
    const EVENT_HELLO = 'hello';
    public function bar()
    {
        $this->trigger(self::EVENT_HELLO);
    }
}
```

在SiteController中的actionIndex()中，添加如下代码，首先创建Foo对象并绑定函数，然后调用foo的bar函数时会触发事件。
```php
        $foo = new Foo();
        $foo->on(Foo::EVENT_HELLO, [$this, 'function_name'], 'abc');
        $foo->bar();
```

### 多台应用服务器csrf会导致登录表单出现问题
修改common\config\main.php，配置request禁用，针对整个项目
```php
        'request' => [
            'enableCsrfValidation' => false,
        ],
```

在控制器中禁用，针对该控制器
```php
class SiteController extends Controller
{
    public $enableCsrfValidation = false;
}
```
http://www.cnblogs.com/ganiks/p/yii2-request-csrf-safe-strategy.html
csrf详解

### Yii事件触发
Yii事件主要是为了协调框架和用户代码之间的注入，如要将用户的代码插入到框架中而不用改动框架代码。
先定义事件
```
<?php
namespace backend\controllers;
use yii\base\Component;
use yii\base\Event;
class Foo extends Component
{
    const EVENT_HELLO = 'hello';
    public function bar()
    {
        $this->trigger(self::EVENT_HELLO);
    }
}
```

在其他地方绑定事件对应的处理，在第三个地方调用$foo->bar()函数时会处理
```
        $foo = new Foo;
        $foo->on(Foo::EVENT_HELLO, function ($event) {
            echo $event->data;
        }, 'abc');
        $foo->bar();
```


### 记录日志，在runtime目录的app.log
Yii::getLogger()->log('Your Log', Logger::LEVEL_ERROR);


### 后台手工添加的字段如何增加过滤器
在对应的ModelSearch.php文件中rules()方法中的safe验证器增加对应的字段。
[['username', 'surname', 'phone', 'auth_key', 'password_hash', 'password_reset_token', 'email', 'role', 'created_at',], 'safe'],
这样在后台list页面显示对应的字段下方会有相关的搜索框。


### 多台服务器assets发布到不同目录
在web/assets/目录生成不同的目录，前台访问时会样式或js错误，这是因为
yii2\web\AssetManager.php的publishDirectory的目录是根据文件名和文件时间
$dir = $this->hash($src . filemtime($src));
如果存在这种行为，最好由一台机器的vendor目录打包后传到其他服务器以保证文件和目录的时间一致。


### 后台使用GridView中自定义按钮
```
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {area}',
                'buttons' => [
                    'area' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-list"></span>', $url, [
                            'title' => Yii::t('app', 'Area'),
                        ]);
                    }
                ],
                'urlCreator' => function ($action, $model, $key, $index) {
                    if ($action === 'view') {
                        return ['view', 'id' => $model->id];
                    } else if ($action === 'update') {
                        return ['update', 'id' => $model->id];
                    } else if ($action === 'area') {
                        return ['area/index', 'group_id' => $model->id];
                    }
                }
            ],
```


### Yii2 SMTP配置 gmail




###  三秒后跳转
```
<p>
    <a href="<?= $redirect ?>"><span id="time">3</span>秒后即将跳转页面</a>
</p>
<?php $this->registerJs('
var time = 3,
    $time = $("#time");
setInterval(function(){
    $time.html(time--);
    if (time < 0) {
        window.location.href="' . $redirect . '"
    }
}, 1000)
') ?>
```


### 解决更新时afterSave的$changedAttributes包含所有未更改的整型数据、浮点数据等
由于post之后，数据的数据都会在类中以string类型存储，但是对于旧属性，会跟数据库保持一致为int，所以新旧数据类型不一样，即使看起来一样，系统用!==判定会是不一样。
在保存之前，对$model中的数据进行转换为对应的类型。
```
$model->company_id = intval($model->company_id);
$model->save();
```

### 支持Email登录
修改/common/models/User.php
```
    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }
```
修改/common/models/LoginForm.php
```
    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
            if(!$this->_user)
                $this->_user = User::findByEmail($this->username);
        }
        return $this->_user;
    }
```

### 在当前页面显示操作信息
```
Yii::$app->session->setFlash('success', 'OK');   //'error'
```

### Yii2-GridView 中让关联字段带搜索和排序功能
Order Model中包含以下代码：
```
public function getCustomer()
{
    return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
}
```
在OrderSearch添加一个$customer_name变量
```
class OrderSearch extends Order
{
    public $customer_name; //<=====就是加在这里
}
```
修改OrderSearch中的search函数
```
public function search($params)
{
    $query =  Order::find();
    $query->joinWith(['customer']);<=====加入这句
    $dataProvider = new ActiveDataProvider([
        'query' => $query,
    ]);
    
    $dataProvider->setSort([
        'attributes' => [
            /* 其它字段不要动 */    
            /*  下面这段是加入的 */
            /*=============*/
            'customer_name' => [
                'asc' => ['customer.customer_name' => SORT_ASC],
                'desc' => ['customer.customer_name' => SORT_DESC],
                'label' => 'Customer Name'
            ],
            /*=============*/
        ]
    ]); 
    if (!($this->load($params) && $this->validate())) {
        return $dataProvider;
    }
    $query->andFilterWhere([
        'id' => $this->id,
        'user_id' => $this->user_id,
        'customer_id' => $this->customer_id,
        'order_time' => $this->order_time,
        'pay_time' => $this->pay_time,
    ]);
    $query->andFilterWhere(['like', 'status', $this->status]);
     $query->andFilterWhere(['like', 'customer.customer_name', $this->customer_name]) ;//<=====加入这句
    
    return $dataProvider;
}
```
第三步：
修改order/index视图的gridview
```
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'id',
        'customer_id',  
        'status',
        ['label'=>'客户',  'attribute' => 'customer_name',  'value' => 'customer.customer_name' ],//<=====加入这句
        ['class' => 'yii\grid\ActionColumn'],
    ],
]); ?>
```


### 分类获取状态为正常的多个商品
```
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['category_id' => 'id'])->where(['status' => Status::STATUS_ACTIVE]);
    }
```

### 批量删除或操作
在product/index.php文件头部
```
<p>
        <?= Html::a(Yii::t('app', 'Batch ') . Yii::t('app', 'Delete'), '#', ['class' => 'btn btn-danger', 'id' => 'batchDelete']) ?>
</p>

<?php
$script = '
    jQuery(document).ready(function() {
      $("#batchDelete").click(function() {
        var keys = $("#w0").yiiGridView("getSelectedRows");
        $.ajax({
            type: "POST",
            url: "' . \yii\helpers\Url::to(['/product/batch-delete']) . '",
            dataType: "json",
            data: {ids: keys}
        });
      });
    });';
$this->registerJs($script, \yii\web\View::POS_END);
```

在ProductController.php文件中
```
    public function actionBatchDelete()
    {
        //if(!Yii::$app->user->can('deleteYourAuth')) throw new ForbiddenHttpException(Yii::t('app', 'No Auth'));

        $ids = Yii::$app->request->post('ids');
        if (is_array($ids)) {
            foreach ($ids as $id) {
                /*$this->findModel($id)->delete();*/
                $model = $this->findModel($id);
                $model->status = Status::STATUS_DELETED;
                $model->save();
            }
        }

        return $this->redirect(['index']);
    }
```


### 使用其他登录地址作为登录入口
全局使用，修改main.php
```
'components' => [
    'user' => [
        'loginUrl' => ['site/sign-in'],  
    ],
],
```
如果只是某一个控制器或方法：
```
Yii::$app->user->loginUrl = ['site/sign-in'];
```


### 手动安装扩展流程
1) Download archive of needed version from Github.
2) Open composer.json. 
3) Find PSR-4 autoload section and remember it, in your case: kartik/select2. 
4) Extract files to corresponding folder in vendor: vendor/kartik/select2 (not yiisoft!). 
5) Add to vendor/composer/autoload_psr4.php: 
'kartik\\select2\\' => array($vendorDir . '/kartik/select2'), 
6) Add to vendor/yiisoft/extensions.php:
'kartik/select2' => array ( 
    'name' => 'kartik/select2',
    'version' => '2',
    'alias' => array (
        '@kartik/select2' => $vendorDir . '/kartik/select2',
    ),
),

### 非www域名跳转到www域名
在frontend/web/.htaccess文件中添加
```
RewriteEngine on

RewriteCond %{HTTP_HOST} ^jiajiayoupin\.com
RewriteRule (.*) http://www.jiajiayoupin.com/$1 [R=301,L]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php
```
这种方法其实不大管用，不如在nginx中中配置：
```
    server {
        server_name sanban98.com;
        location / {
            rewrite (.*) http://www.sanban98.com$1 permanent;
        }
    }
```


### 单独生成一个sitemap.xml文件
http://www.bsourcecode.com/yiiframework2/url-manager-in-yiiframework-2-0/#configure-url-rules 
在main.php中单独定义sitemap.xml的url规则
```
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'suffix' => '.html',
            //'enableStrictParsing' => true,
            'rules' => [
                [
                    'pattern' => 'sitemap',
                    'route' => 'site/sitemap',
                    'suffix' => '.xml',
                ],
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
```

### 如何使用min.js文件
在config/main.php文件中
```
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'js' => [
                        'jquery.min.js'
                    ]
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'js' => [
                        'bootstrap.min.js'
                    ]
                ],
            ],
        ],
    ]
```

### 日志如何去除_SERVER等信息
配置config.php文件
```
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['warning'],
                    'logVars' => [],
                    'logFile' => '@runtime/logs/warning.log',
                    //'enableDatePrefix' => true,
                ],
            ],
        ],
    ],
```


第三方登录
1.coposer安装https://github.com/xjflyttp/yii2-oauth
2.在QQ和微博上申请id
3.修改front/config/main.php
```
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'qq' => [
                    'class' => 'xj\oauth\QqAuth',
                    'clientId' => '101257542',
                    'clientSecret' => '3ab0e4a3528e9e5731a726366ae367b6',

                ],
                /*'sina' => [
                    'class' => 'xj\oauth\SinaAuth',
                    'clientId' => '111',
                    'clientSecret' => '111',
                ],
                'weixin' => [
                    'class' => 'xj\oauth\WeixinAuth',
                    'clientId' => '111',
                    'clientSecret' => '111',
                ],*/
            ],
        ],
```
 在urlManager中增加'site/auth/<authclient:\w+>' => 'site/auth',作为回调地址，访问地址为http://www.sanban98.com/site/auth/qq.html了。
4.在SiteController中的actions中增加
```
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'successCallback'],
            ],
```
和函数
```
    /**
     * Success Callback
     * @param QqAuth|WeiboAuth $client
     * @see http://wiki.connect.qq.com/get_user_info
     * @see http://stuff.cebe.cc/yii2docs/yii-authclient-authaction.html
     */
    public function successCallback($client) {
        $id = $client->getId(); // qq | sina | weixin
        $attributes = $client->getUserAttributes(); // basic info
        $userInfo = $client->getUserInfo(); // user extend info

        $openId = isset($attributes['openid']) ? $attributes['openid'] : $attributes['uid'];
        $accessToken = md5($openId);
        $user = User::find()->where(['username' => $openId])->one();
        if ($user) {
            Yii::$app->user->loginByAccessToken($accessToken);
        } else {
            if ($id == 'qq') {
                $email = $openId . '@qq.com';
                $surname = $userInfo['nickname'];
            } elseif ($id == 'weibo') {
                $email = $openId . '@weibo.com';
                $surname = $userInfo['name'];
            }
            $user = new User([
                'username' => $openId,
                'auth_key' => $accessToken,
                'access_token' => $accessToken,
                'password_hash' => $accessToken,
                'email' => $email,
            ]);
            $user->save();
            $profile = new Profile([
                'user_id' => $user->id,
                'surname' => $surname,
            ]);
            $profile->save();

            Yii::$app->user->loginByAccessToken($accessToken);
        }
    }
```

5.修改common/models/User.php支持access_token登录
```
     public static function findIdentityByAccessToken($token, $type = null)
     {
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
     }
```

6.在login.php中增加
```
                <?=
                yii\authclient\widgets\AuthChoice::widget([
                    'baseAuthUrl' => ['site/auth'],
                    'popupMode' => false,
                ])
                ?>
```

7.在main.php或其他地方需要显示surname的地方修改
```
                    'label' => isset(Yii::$app->user->identity->profile->surname) ? Yii::$app->user->identity->profile->surname : Yii::$app->user->identity->username,
```


### 上传多个图片设置
上传图片过多时会报错，需要修改php.ini几处的设置
```
post_max_size = 100M  单次POST提交最大数据量
upload_max_filesize = 100M  每个文件最大量
max_file_uploads = 300  单词可提交的文件数量
```


### 邮件发送
使用swiftmailer的smtp方式发送邮件最为方便，在config/main.php中compnent下配置：
```
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => false,//set this property to false to send mails to real email addresses
            //comment the following array to send mail using php's mail function
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.exmail.qq.com',
                'username' => 'funson@iwalnuts.com',
                'password' => 'A**8',
                'port' => '587',
                'encryption' => 'tls',
            ],
        ],
```

在controller中写如下代码即可发送邮件
```
    public function actionTestMail()
    {
        $user = \common\models\User::findOne(1);
        Yii::$app->mail->compose('passwordResetToken', ['user' => $user])
            ->setFrom(['funson@iwalnuts.com' => 'Test Mail'])
            ->setTo('lifs@lieying.cn')
            ->setSubject('This is a test mail ' )
            ->send();
    }
```

### Yii2 queue队列
支持好多种队列方式，最常用的redis方式如下：
1.安装yii queue组件  php composer.phar require --prefer-dist yiisoft/yii2-queue
2.安装yii redis组件 php composer.phar require --prefer-dist yiisoft/yii2-redis
3.在common/config/main.php中配置redis信息
```
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ]
```
4.在common/config/main.php中配置queue信息
```
    'components' => [
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'as log' => \yii\queue\LogBehavior::class,
            // Other driver options
        ],
    ],
```
5.在console/config/main.php中配置
    'bootstrap' => ['queue'],
运行：php yii queue/listen
6.验证可用，在console/job目录下新建文件DownloadJob.php
```
namespace console\job;

use yii\base\BaseObject;

class DownloadJob extends BaseObject implements \yii\queue\JobInterface
{
    public $url;
    public $file;

    public function execute($queue)
    {
        file_put_contents($this->file, file_get_contents($this->url));
    }
}
```

在controller中增加如下代码进行测试
```
    public function actionTestQueue()
    {
        Yii::$app->queue->push(new DownloadJob([
            'url' => 'http://www.baidu.com',
            'file' => 'e:\\test.txt',
        ]));
    }
```

### LeftJoin 拼接字符串太丑，特别是要写case when进行排序时
```
$models = Task::find()
    ->where([Task::tableName() . '.status' => Task::STATUS_ACTIVE])
    ->andWhere(['&', 'platform', $platform])
    ->leftJoin(UserTask::tableName(), UserTask::tableName() . '.user_id = ' . $user->id . ' AND ' . UserTask::tableName() . '.task_id = ' . Task::tableName() . '.id')
    ->select([Task::tableName() . '.*', UserTask::tableName() . '.current', UserTask::tableName() . '.`status`', UserTask::tableName() . '.finished_at',
    'CASE WHEN ' . UserTask::tableName() . '.`status` = 2 THEN 1 WHEN ' . UserTask::tableName() . '.`status` = 1 THEN 2 WHEN ' . UserTask::tableName() . '.`status` = 3 THEN 3 END AS sort_order'])
    ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_DESC])
    ->asArray()
    ->all();
```





