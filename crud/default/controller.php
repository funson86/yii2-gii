<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;


/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif; ?>
use common\models\BaseModel;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;

/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    private $importPrimary = 'name';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ],
        ];
    }

    /**
     * Lists all <?= $modelClass ?> models.
     * @return mixed
     */
    public function actionIndex()
    {
        /*if (!Yii::$app->user->can('viewYourAuth')) {
            throw new ForbiddenHttpException(Yii::t('app', 'No Auth'));
        }*/

<?php if (!empty($generator->searchModelClass)): ?>
        $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
<?php else: ?>
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelClass ?>::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
<?php endif; ?>
    }

    /**
     * Displays a single <?= $modelClass ?> model.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionView(<?= $actionParams ?>)
    {
        /*if (!Yii::$app->user->can('viewYourAuth')) {
            throw new ForbiddenHttpException(Yii::t('app', 'No Auth'));
        }*/

        return $this->render('view', [
            'model' => $this->findModel(<?= $actionParams ?>),
        ]);
    }

    /**
     * Creates a new <?= $modelClass ?> model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        /*if (!Yii::$app->user->can('createYourAuth')) {
            throw new ForbiddenHttpException(Yii::t('app', 'No Auth'));
        }*/

        $model = new <?= $modelClass ?>();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', <?= $urlParams ?>]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing <?= $modelClass ?> model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionUpdate(<?= $actionParams ?>)
    {
        /*if (!Yii::$app->user->can('updateYourAuth')) {
            throw new ForbiddenHttpException(Yii::t('app', 'No Auth'));
        }*/

        $model = $this->findModel(<?= $actionParams ?>);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', <?= $urlParams ?>]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing <?= $modelClass ?> model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionDelete(<?= $actionParams ?>)
    {
        /*if (!Yii::$app->user->can('deleteYourAuth')) {
            throw new ForbiddenHttpException(Yii::t('app', 'No Auth'));
        }*/

        $this->findModel(<?= $actionParams ?>)->delete();
        /*$model = $this->findModel(<?= $actionParams ?>);
        $model->status = Status::STATUS_DELETED;
        $model->save();*/

        return $this->redirect(['index']);
    }

    /**
     * Finds the <?= $modelClass ?> model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return <?=                   $modelClass ?> the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(<?= $actionParams ?>)
    {
<?php
if (count($pks) === 1) {
    $condition = '$id';
} else {
    $condition = [];
    foreach ($pks as $pk) {
        $condition[] = "'$pk' => \$$pk";
    }
    $condition = '[' . implode(', ', $condition) . ']';
}
?>
        if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * batch import product
     * @return mixed
     */
    public function actionImport()
    {
        //if(!Yii::$app->user->can('viewYourAuth')) throw new ForbiddenHttpException(Yii::t('app', 'No Auth'));
        $model = new <?= $modelClass ?>();
        $model->loadDefaultValues();

        if (Yii::$app->request->isPost) {
            $countCreate = $countUpdate = 0;

            $file = UploadedFile::getInstanceByName('importFile');
            if (empty($file)) {
                Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'No Files, please check file.'));
                return $this->render('import', [
                    'model' => $model,
                ]);
            }
            $handle = fopen($file->tempName, 'r');
            $result = BaseModel::inputCsv($handle);

            $arrData = [];
            if (count($result) <= 1) {
                Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'No Record, please check file.'));
            } else {
                $line = 2;
                $errorLines = [];
                $existsName = [];

                for ($i = 1; $i < count($result); $i++) {
                    if (false/*empty($result[$i][0]) || empty($result[$i][1])*/) {
                        array_push($errorLines, $line);
                        continue;
                    }
                    $line++;

                    array_push($arrData, $result[$i]);
                }

                $relationNameId = '';
                $importFields = <?= $modelClass ?>::getImportFields();
                foreach ($importFields as $field => $fieldType) {
                    if ($fieldType == 'relation' && $field == 'parent_id') {
                        $modelName = "\\common\\models\\<?= $modelClass ?>";
                        $relationNameId[$field] = ArrayHelper::map($modelName::find()->all(), 'name', 'id');
                    } elseif ($fieldType == 'relation') {
                        $modelName = "\\common\\models\\" . Inflector::camelize(Inflector::humanize($field));
                        $name = in_array($field, ['user_id', 'created_by', 'updated_by']) ? 'username' : 'name';
                        $relationNameId[$field] = ArrayHelper::map($modelName::find()->all(), $name, 'id');
                    }
                }

                foreach ($arrData as $item) {
                    $i = 0;
                    $fields = [];
                    foreach ($importFields as $field => $fieldType) {
                        if ($fieldType == 'text') {
                            $fields[$field] = mb_convert_encoding(trim($item[$i]), 'utf-8', 'gb2312,gbk');
                        } elseif ($fieldType == 'enum') {
                            $func = 'get' . Inflector::camelize($field) . 'Labels';
                            $arrayLabelsId = array_flip(call_user_func(["\\common\\models\\<?= $modelClass ?>", $func]));
                            $raw = mb_convert_encoding(trim($item[$i]), 'utf-8', 'gb2312,gbk');
                            $fields[$field] = isset($arrayLabelsId[$raw]) ? $arrayLabelsId[$raw] : 0;
                        } elseif ($fieldType == 'relation') {
                            $raw = mb_convert_encoding(trim($item[$i]), 'utf-8', 'gb2312,gbk');
                            $fields[$field] = isset($relationNameId[$field][$raw]) ? $relationNameId[$field][$raw] : 0;
                        } elseif ($fieldType == 'int') {
                            $fields[$field] = intval(trim($item[$i]));
                        } elseif ($fieldType == 'decimal') {
                            $fields[$field] = floatval(trim($item[$i]));
                        }

                        $i++;
                    }
                    //查看是否存在,如果存在不导入
                    $count = <?= $modelClass ?>::find()->where([$this->importPrimary => $fields[$this->importPrimary]])->count();
                    if ($count > 0) {
                        $model = <?= $modelClass ?>::find()->where([$this->importPrimary => $fields[$this->importPrimary]])->one();
                        $model->attributes = $fields;
                        $result = $model->save();
                        if (!$result) { //如果保存失败
                            array_push($errorLines, $line);
                            $line++;
                            continue;
                        }
                        array_push($existsName, $fields['name']);
                        $countUpdate++;
                        continue;
                    }

                    $model->attributes = $fields;
                    $result = $model->save();
                    if (!$result) { //如果保存失败
                        array_push($errorLines, $line);
                        $line++;
                        continue;
                    }
                    $countCreate++;

                    $line++;
                }

                if (count($existsName)) {
                    $existsName = implode(',', $existsName);
                    Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'Record ' . $existsName . ' Updated.'));
                }
                if (count($errorLines)) {
                    $strLine = implode(', ', $errorLines);
                    Yii::$app->getSession()->setFlash('danger', Yii::t('app', "Line {strLine} error.", ['strLine' => $strLine]));
                }
                Yii::$app->getSession()->setFlash('success', Yii::t('app', "Import Data Success. Create: {countCreate}  Update: {countUpdate}", ['countCreate' => $countCreate, 'countUpdate' => $countUpdate]));
            }
        }

        return $this->render('import', [
            'model' => $model,
        ]);
    }


    /**
     * batch import product
     * @return mixed
     */
    public function actionExport()
    {
        $str = '';
        $model = new <?= $modelClass ?>();

        $relationIdName = [];
        $exportFields = <?= $modelClass ?>::getExportFields();
        foreach ($exportFields as $field => $fieldType) {
            if ($str == '') {
                $str .= '"' . mb_convert_encoding($model->attributeLabels()[$field], 'gbk', 'utf-8') . '"';
            } else {
                $str .= ',"' . mb_convert_encoding($model->attributeLabels()[$field], 'gbk', 'utf-8') . '"';
            }

            if ($fieldType == 'relation' && $field == 'parent_id') {
                $modelName = "\\common\\models\\<?= $modelClass ?>";
                $relationIdName[$field] = ArrayHelper::map($modelName::find()->all(), 'id', 'name');
            } elseif ($fieldType == 'relation') {
                $modelName = "\\common\\models\\" . Inflector::camelize(Inflector::humanize($field));
                $name = in_array($field, ['user_id', 'created_by', 'updated_by']) ? 'username' : 'name';
                $relationIdName[$field] = ArrayHelper::map($modelName::find()->all(), 'id', $name);
            }
        }
        $str .= "\n";

        $models = <?= $modelClass ?>::find()->orderBy(['id' => SORT_ASC])->all();
        foreach ($models as $model) {
            $line = '';
            foreach ($exportFields as $field => $fieldType) {
                $value = '';
                if ($fieldType == 'text') {
                    $value = mb_convert_encoding($model->$field, 'gbk', 'utf-8');
                } elseif ($fieldType == 'enum') {
                    $func = 'get' . Inflector::camelize($field) . 'Labels';
                    $arrayIdLabels = call_user_func(["\\common\\models\\<?= $modelClass ?>", $func]);
                    $value = mb_convert_encoding($arrayIdLabels[$model->$field], 'gbk', 'utf-8');
                } elseif ($fieldType == 'relation') {
                    $value = isset($relationIdName[$field][$model->$field]) ? $relationIdName[$field][$model->$field] : '';
                    $value = mb_convert_encoding($value, 'gbk', 'utf-8');
                } elseif ($fieldType == 'int') {
                    $value = intval(trim($model->$field));
                } elseif ($fieldType == 'decimal') {
                    $value = floatval(trim($model->$field));
                }

                if ($line == '') {
                    $line .= '"' . $value . '"';
                } else {
                    $line .= ',"' . str_replace("\"", "\"\"", $value) . '"';
                }
            }
            $str .= $line;
            $str .= "\n";
        }

        $filename = date('YmdHi') . '.csv';

        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;
    }
}
