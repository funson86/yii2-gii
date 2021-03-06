<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\ArrayHelper;

$labelList = ['type', 'kind'];
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use <?= $generator->modelClass ?>;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-6\">{input}{hint}</div>\n<div class=\"col-lg-5\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

<?php foreach ($generator->getColumnNames() as $attribute) {
    if (in_array($attribute, $safeAttributes)) {
        if ($attribute == 'id' || strrchr($attribute, '_at') == '_at' || strrchr($attribute, '_by') == '_by') {
            continue;
        } elseif (strrchr($attribute, '_id') == '_id') {
            $modelStr = Inflector::camelize(Inflector::humanize($attribute));
            $relation = Inflector::variablize(Inflector::humanize($attribute));
            if ($attribute == 'parent_id') {
                $modelStr = StringHelper::basename($generator->modelClass);
                $relation = 'parent';
            }

            echo "    <?= \$form->field(\$model, '" . $attribute . "')->dropDownList(ArrayHelper::map(\\common\\models\\" . $modelStr . "::find()->all(), 'id', '" . ($attribute == 'user_id' ? 'username' : 'name') . "'), ['prompt' => Yii::t('app', 'Please Select')]) ?>\n\n";
        } elseif (strpos($attribute, 'status') !== false || in_array($attribute, $labelList)) {
            $modelStr = Inflector::camelize(Inflector::humanize($attribute));
            $relation = Inflector::variablize(Inflector::humanize($attribute));

            echo "    <?= \$form->field(\$model, '" . $attribute . "')->dropDownList(" . $generator->modelClass ."::get" . $modelStr ."Labels(), ['prompt' => Yii::t('app', 'Please Select')]) ?>\n\n";
        } else {
            echo "    <?= " . $generator->generateActiveField($attribute) . " ?>\n\n";
        }
    }
} ?>
    <div class="form-group">
        <label class="col-lg-2 control-label" for="">&nbsp;</label>
        <?= "<?= " ?>Html::submitButton($model->isNewRecord ? <?= $generator->generateString('Create') ?> : <?= $generator->generateString('Update') ?>, ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
