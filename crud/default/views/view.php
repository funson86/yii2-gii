<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$labelList = ['type', 'kind'];
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;
use <?= $generator->modelClass ?>;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">

    <p>
        <?= "<?= " ?>Html::a(Yii::t('app', <?= $generator->generateString('Update') ?>), ['update', <?= $urlParams ?>], ['class' => 'btn btn-primary']) ?>
        <?= "<?= " ?>Html::a(Yii::t('app', <?= $generator->generateString('Delete') ?>), ['delete', <?= $urlParams ?>], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>,
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= "<?= " ?>DetailView::widget([
        'model' => $model,
        'attributes' => [
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if (strrchr($name, '_at') == '_at') {
            echo "            '" . $name . ":datetime',\n";
        } elseif (strrchr($name, '_by') == '_by') {
            $modelName = $name;
            $arrName = explode('_', $modelName);
            $modelStr = $relation = '';
            foreach ($arrName as $item) {
                $modelStr .= ucfirst(strtolower($item));
                if ($relation == '') {
                    $relation = strtolower($item);
                } else {
                    $relation .= ucfirst(strtolower($item));
                }
            }
            echo "            [\n";
            echo "                'attribute' => '" . $name . "',\n";
            echo "                'value' => $model->" . $modelStr . "->username,\n";
            echo "            ],\n";
        } else {
            echo "            '" . $name . "',\n";
        }
    }
} else {
    foreach ($generator->getTableSchema()->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        if (strrchr($column->name, '_at') == '_at') {
            echo "            '" . $column->name . ":datetime',\n";
        } elseif (strrchr($column->name, '_by') == '_by') {
            $modelStr = Inflector::camelize(Inflector::humanize($column->name));
            $relation = Inflector::variablize(Inflector::humanize($column->name));

            echo "            [\n";
            echo "                'attribute' => '" . $column->name . "',\n";
            echo "                'value' => \$model->" . $relation . "->username,\n";
            echo "            ],\n";
        } elseif (strrchr($column->name, '_id') == '_id') {
            $modelStr = Inflector::camelize(Inflector::humanize($column->name));
            $relation = Inflector::variablize(Inflector::humanize($column->name));

            echo "            [\n";
            echo "                'attribute' => '" . $column->name . "',\n";
            echo "                'value' => isset(\$model->" . $relation . ") ? \$model->" . $relation . "->" . ($column->name == 'user_id' ? 'username' : 'name') . " : \$model->" . $column->name . ",\n";
            echo "            ],\n";
        } elseif (strpos($column->name, 'status') !== false || in_array($column->name, $labelList)) {
            $modelStr = Inflector::camelize(Inflector::humanize($column->name));
            $relation = Inflector::variablize(Inflector::humanize($column->name));

            echo "            [\n";
            echo "                'attribute' => '" . $column->name ."',\n";
            echo "                'value' => " . $generator->modelClass ."::get" . $modelStr . "Labels(\$model->" . $column->name ."),\n";
            echo "            ],\n";
        } else {
            echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        }
    }
}
?>
        ],
    ]) ?>

</div>
