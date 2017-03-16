<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$labelList = ['type', 'kind', 'product_type'];
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>

use yii\helpers\Html;
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
use yii\helpers\ArrayHelper;
use <?= $generator->modelClass ?>;

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">

<?php if(!empty($generator->searchModelClass)): ?>
<?= "    <?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>

    <p>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Create ') ?> . <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>, ['create'], ['class' => 'btn btn-success']) ?>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Import ') ?> . <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>, ['import'], ['class' => 'btn btn-success']) ?>
        <?= "<?= " ?>Html::a(Yii::t('app', 'Download Template'), '/template/<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>.csv', ['class' => 'btn btn-primary', 'target' => '_blank']) ?>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Export ') ?> . <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>, ['export'], ['class' => 'btn btn-success', 'target' => '_blank']) ?>
    </p>

<?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= "<?= " ?>GridView::widget([
        'dataProvider' => $dataProvider,
        <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n        'columns' => [\n" : "'columns' => [\n"; ?>
            ['class' => 'yii\grid\SerialColumn'],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if (++$count < 6) {
            echo "            '" . $name . "',\n";
        } else {
            echo "            // '" . $name . "',\n";
        }
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        if (strrchr($column->name, '_at') == '_at') {
            echo "            '" . $column->name . ":datetime',\n";
        } elseif (strrchr($column->name, '_by') == '_by') {
            $modelStr = Inflector::camelize(Inflector::humanize($column->name));
            $relation = Inflector::variablize(Inflector::humanize($column->name));

            echo "            [\n";
            echo "                'attribute' => '" . $column->name . "',\n";
            echo "                'value' => function (\$model) {\n";
            echo "                    return \$model->" . $relation . " ? \$model->" . $relation . "->username : '-';\n";
            echo "                },\n";
            echo "            ],\n";
        } elseif (strrchr($column->name, '_id') == '_id') {
            $modelStr = Inflector::camelize(Inflector::humanize($column->name));
            $relation = Inflector::variablize(Inflector::humanize($column->name));
            if ($column->name == 'parent_id') {
                $modelStr = StringHelper::basename($generator->modelClass);
                $relation = 'parent';
            }

            echo "            [\n";
            echo "                'attribute' => '" . $column->name ."',\n";
            echo "                'value' => function (\$model) {\n";
            echo "                    return \$model->" . $relation ." ? \$model->" . $relation ."->" . ($column->name == 'user_id' ? 'username' : 'name') . " : \$model->" . $column->name .";\n";
            echo "                },\n";
            echo "                'filter' => Html::activeDropDownList(\n";
            echo "                    \$searchModel,\n";
            echo "                    '" . $column->name ."',\n";
            echo "                    ArrayHelper::map(\\common\\models\\" . $modelStr ."::find()->where(['id' => ArrayHelper::getColumn(" . StringHelper::basename($generator->modelClass) . "::find()->all(), '" . $column->name . "')])->all(), 'id', '" . ($column->name == 'user_id' ? 'username' : 'name') . "'),\n";
            echo "                    ['class' => 'form-control', 'prompt' => Yii::t('app', 'Please Filter')]\n";
            echo "                )\n";
            echo "            ],\n";
        } elseif (strpos($column->name, 'status') !== false || in_array($column->name, $labelList)) {
            $modelStr = Inflector::camelize(Inflector::humanize($column->name));
            $relation = Inflector::variablize(Inflector::humanize($column->name));

            echo "            [\n";
            echo "                'attribute' => '" . $column->name ."',\n";
            echo "                'format' => 'html',\n";
            echo "                'value' => function (\$model) {\n";
            if (strpos($column->name, 'status') !== false) {
                echo "                    if (\$model->" . $column->name ." === " . $generator->modelClass . "::" . strtoupper($column->name) ."_ACTIVE) {\n";
                echo "                        \$class = 'label-success';\n";
                echo "                    } elseif (\$model->" . $column->name ." === " . $generator->modelClass . "::" . strtoupper($column->name) ."_INACTIVE) {\n";
                echo "                        \$class = 'label-warning';\n";
                echo "                    } else {\n";
                echo "                        \$class = 'label-danger';\n";
                echo "                    }\n";
                echo "\n";
                echo "                    return '<span class=\"label ' . \$class . '\">' . " . $generator->modelClass . "::get" . $modelStr . "Labels(\$model->" . $column->name . ") . '</span>';\n";
            } else {
                echo "                     return " . $generator->modelClass . "::get" . $modelStr . "Labels(\$model->" . $column->name . ");\n";
            }
            echo "                },\n";
            echo "\n";
            echo "                'filter' => Html::activeDropDownList(\n";
            echo "                    \$searchModel,\n";
            echo "                    '" . $column->name ."',\n";
            echo "                    " . $generator->modelClass ."::get" . $modelStr ."Labels(),\n";
            echo "                    ['class' => 'form-control', 'prompt' => Yii::t('app', 'PROMPT_STATUS')]\n";
            echo "                )\n";
            echo "            ],\n";
        } else {
            echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        }
    }
}
?>

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php else: ?>
    <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
        },
    ]) ?>
<?php endif; ?>

</div>
