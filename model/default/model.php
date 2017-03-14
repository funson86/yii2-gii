<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

$atList = ['created_at', 'updated_at'];
$byList = ['created_by', 'updated_by'];
$labelList = ['type', 'kind', 'product_type'];
$isAt = $isBy = $isLabel = $isParent = false;
foreach ($tableSchema->columns as $column) {
    if (in_array($column->name, $atList)) {
        $isAt = true;
    }
    if (in_array($column->name, $byList)) {
        $isBy = true;
    }
    if (in_array($column->name, $labelList)) {
        $isLabel = true;
    }
    if ($column->name == 'parent_id') {
        $isParent = true;
    }
}

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php if ($isBy): ?>
 * @property User $createdBy
 * @property User $updatedBy
<?php endif; ?>
<?php endif; ?>
 */
class <?= $className ?> extends BaseModel
{
<?php if ($isLabel): ?>
<?php foreach($tableSchema->columns as $column): ?>
<?php if (in_array($column->name, $labelList)): ?>
    const <?= strtoupper($column->name) ?>_A = 1;
    const <?= strtoupper($column->name) ?>_B = 0;
    const <?= strtoupper($column->name) ?>_C = -1;

<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '<?= $generator->generateTableName($tableName) ?>';
    }

    /**
     * create_time, update_time to now()
     * crate_user_id, update_user_id to current login user id
     */
    public function behaviors()
    {
        return [
<?php if ($isAt): ?>
            TimestampBehavior::className(),
<?php endif; ?>
<?php if ($isBy): ?>
            BlameableBehavior::className(),
<?php endif; ?>
        ];
    }
<?php if ($generator->db !== 'db'): ?>

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . "\n        " ?>];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
        ];
    }

    /**
     * set import csv by sort. (name => type)
     * type relation:xxx_id  enum:$labelList  text:string  int:integer
     *
     * @return array attribute type (name => type)
     */
    public static function getImportFields()
    {
        return [
<?php foreach ($tableSchema->columns as $column) {
    $name = $column->name;
    $type = $column->phpType;
    if ($name === 'id' || strrchr($name, '_at') == '_at' || strrchr($name, '_by')  == '_by') {
        continue;
    } elseif (in_array($name, $labelList)) {
        echo "            '{$name}' => 'enum',\n";
    } elseif (strrchr($name, '_id') == '_id') {
        echo "            '{$name}' => 'relation',\n";
    } elseif (in_array($type, ['integer', 'boolean'])) {
        echo "            '{$name}' => 'int',\n";
    } else {
        echo "            '{$name}' => 'text',\n";
    }
}
?>
        ];
    }

    /**
     * set export csv by sort. (name => type)
     * type relation:xxx_id  enum:$labelList  text:string  int:integer
     *
     * @return array attribute type (name => type)
     */
    public static function getExportFields()
    {
        return [
<?php foreach ($tableSchema->columns as $column) {
    $name = $column->name;
    $type = $column->phpType;
    if (strrchr($name, '_at') == '_at' || strrchr($name, '_by')  == '_by') {
        continue;
    } elseif (in_array($name, $labelList)) {
        echo "            '{$name}' => 'enum',\n";
    } elseif (strrchr($name, '_id') == '_id') {
        echo "            '{$name}' => 'relation',\n";
    } elseif (in_array($type, ['integer', 'boolean'])) {
        echo "            '{$name}' => 'int',\n";
    } else {
        echo "            '{$name}' => 'text',\n";
    }
}
?>
        ];
    }
<?php foreach ($relations as $name => $relation): ?>

    /**
     * @return \yii\db\ActiveQuery
     */
    public function get<?= $name ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
<?php if ($isParent): ?>

    /**
    * @return \yii\db\ActiveQuery
    */
    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_id']);
    }
<?php endif; ?>
<?php if ($isBy): ?>

    /**
    * @return \yii\db\ActiveQuery
    */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
    * @return \yii\db\ActiveQuery
    */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }
<?php endif; ?>
<?php if ($isLabel): ?>
<?php foreach($tableSchema->columns as $column): ?>
<?php if (in_array($column->name, $labelList)): ?>

    /**
     * return label or labels array
     *
     * @param  integer $id
     * @return string or array
    */
    public static function get<?= \yii\helpers\Inflector::camelize($column->name) ?>Labels($id = null)
    {
        $data = [
            self::<?= strtoupper($column->name) ?>_A => Yii::t('app', '<?= strtoupper($column->name) ?>_A'),
            self::<?= strtoupper($column->name) ?>_B => Yii::t('app', '<?= strtoupper($column->name) ?>_B'),
            self::<?= strtoupper($column->name) ?>_C => Yii::t('app', '<?= strtoupper($column->name) ?>_C'),
        ];

        if ($id !== null && isset($data[$id])) {
            return $data[$id];
        } else {
            return $data;
        }
    }
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>

    /**
     * Before save.
     * 
     */
    /*public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            // add your code here
            return true;
        }
        else
            return false;
    }*/

    /**
     * After save.
     *
     */
    /*public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        // add your code here
    }*/
}
