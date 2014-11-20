Yii2 tips
=======

### 在view文件中createUrl 和Html链接
```
<?php echo \Yii::$app->getUrlManager()->createUrl(['catalog/create', 'parent_id'=>$item['id']]); ?
Html::a(Html::encode($tag), ['blog/index','tag'=>$tag]);
```

### 在view文件_form.php中droplist下拉菜单
```
use yii\helpers\ArrayHelper;
use backend\models\Standard;
<?= $form->field($model, 'center_id')->dropDownList(ArrayHelper::map(Course::find()->asArray()->all(), 'id', 'name'), ['prompt'=> Yii::t('app', 'Please Select') ?>
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
            'defaultPageSize' => 5,
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
<?= Html::activeLabel($user, 'email'); ?>
<?= Html::activeTextInput($user, 'email', ['class' => 'user', 'placeholder' => '请输入帐号/手机号']); ?>
<?= Html::error($user, 'email'); ?>
```

### 简化Gridview中的时间显示，首先设置在应用中设置formatter的格式，然后只需要简单的在create_time修改为create_time:date即可。
```
        'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
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
            ],
        ],
```

### 前端不同的页面的js或css文件不同，则需要注册对应的文件，但是需要在Asset后面，则定义'depends'，js可指定位置
```
$this->registerCssFile('@web/web/css/login.css', ['depends' => \frontend\assets\WebAsset::className()]);
$this->registerJsFile('@web/web/js/check.js', ['depends' => \frontend\assets\WebAsset::className(), 'position' => \yii\web\View::POS_HEAD]);
```








