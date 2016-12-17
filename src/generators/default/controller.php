<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\db\ActiveRecordInterface;


/* @var $this yii\web\View */
/* @var $generator johnitvn\ajaxcrud\generators */

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


$modelClassLabel = $generator->getClassLabel();

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
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * <?= $controllerClass ?> 结合 <?= $modelClass ?> model 生成 CRUD 操作.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'bulk-delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * <?= $modelClass ?> models 记录列表.
     * @return mixed
     */
    public function actionIndex()
    {    
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
     * 查看一个 <?= $modelClass ?> model 详情.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionView(<?= $actionParams ?>)
    {   
        $request = Yii::$app->request;
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                    'title'=> "<?= $generator->generateString($modelClassLabel) ?> #".<?= $actionParams ?>,
                    'content'=>$this->renderAjax('view', [
                        'model' => $this->findModel(<?= $actionParams ?>),
                    ]),
                    'footer'=> $this->getCloseButton() . $this->getUpdateButton($id)
                ];    
        }else{
            return $this->render('view', [
                'model' => $this->findModel(<?= $actionParams ?>),
            ]);
        }
    }

    /**
     * 添加一条<?= $modelClass ?> model的新记录.
     * 如果是 ajax 请求将返回 JSON 对象
     * 如果非 ajax 请求，在保存成功后，将跳转到查看详情页面.
     * @return mixed
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new <?= $modelClass ?>();  

        if($request->isAjax){
            /*
            *   处理 ajax 请求
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            $title = <?= $generator->generateString('新建{modelClass}', ['modelClass' => $modelClassLabel]) ?>;
            if($model->load($request->post()) && $model->save()){
                return [
                    'forceReload'=>'#crud-datatable-pjax',
                    'title'=> $title,
                    'content'=>'<span class="text-success">' . <?= $generator->generateString('新建{modelClass}成功', ['modelClass' => $modelClassLabel]) ?> . '</span>',
                    'footer'=> $this->getCloseButton() . $this->getCreateMoreButton()
        
                ];         
            }else{           
                return [
                    'title'=> $title,
                    'content'=>$this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer'=> $this->getCloseButton() . $this->getSaveButton()
        
                ];         
            }
        }else{
            /*
            *   处理非 ajax 请求
            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', <?= $urlParams ?>]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
       
    }

    /**
     * 编辑已存在的 Model: <?= $modelClass ?> .
     * 如果是 ajax 请求，将返回 JSON 格式
     * 如果非 ajax 请求，保存成功后将跳转到查看页面.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionUpdate(<?= $actionParams ?>)
    {
        $request = Yii::$app->request;
        $model = $this->findModel(<?= $actionParams ?>);       

        if($request->isAjax){
            /*
            *   处理 ajax 请求
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            $title = <?= $generator->generateString('更新{modelClass}', ['modelClass' => $modelClassLabel]) ?> . ' #' . <?= $actionParams ?>;
            if($model->load($request->post()) && $model->save()){
                return [
                    'forceReload'=>'#crud-datatable-pjax',
                    'title'=> $title,
                    'content'=>$this->renderAjax('view', [
                        'model' => $model,
                    ]),
                    'footer'=> $this->getCloseButton() . $this->getUpdateButton($id)
                ];    
            }else{
                 return [
                    'title'=> $title,
                    'content'=>$this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer'=> $this->getCloseButton() . $this->getSaveButton()
                ];        
            }
        }else{
            /*
            *   处理非 ajax 请求
            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', <?= $urlParams ?>]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * 删除一条已存在的记录 <?= $modelClass ?> model.
     * 如果是 ajax 请求，将返回 JSON 格式
     * 如果非 ajax 请求，保存成功后将跳转到列表页面.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionDelete(<?= $actionParams ?>)
    {
        $request = Yii::$app->request;
        $this->findModel(<?= $actionParams ?>)->delete();

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }else{
            /*
            *   处理非 ajax 请求
            */
            return $this->redirect(['index']);
        }


    }

     /**
     * 批量删除已存在的记录 <?= $modelClass ?> model.
     * 如果是 ajax 请求，将返回 JSON 格式
     * 如果非 ajax 请求，保存成功后将跳转到查看页面.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return mixed
     */
    public function actionBulkDelete()
    {        
        $request = Yii::$app->request;
        $pks = explode(',', $request->post( 'pks' )); // Array or selected records primary keys
        foreach ( $pks as $pk ) {
            $model = $this->findModel($pk);
            $model->delete();
        }

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }else{
            /*
            *   处理非 ajax 请求
            */
            return $this->redirect(['index']);
        }
       
    }

    /**
     * 根据主键查找 <?= $modelClass ?> model.
     * 如果未找到，将抛出“404”异常.
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
            throw new NotFoundHttpException('请求的页面不存在.');
        }
    }

    /**
     * @return string
     */
    protected function getCloseButton()
    {
        return Html::button(<?= $generator->generateString('关闭') ?>, ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]);
    }

    /**
     * @param $id
     * @return string
     */
    protected function getUpdateButton($id)
    {
        return Html::a(<?= $generator->generateString('编辑') ?>, ['update', 'id' => $id], ['class' => 'btn btn-primary', 'role' => 'modal-remote']);
    }

    /**
     * @return string
     */
    protected function getSaveButton()
    {
        return Html::button(<?= $generator->generateString('保存') ?>, ['class' => 'btn btn-primary', 'type' => "submit"]);
    }

    /**
     * @return string
     */
    protected function getCreateButton()
    {
        return Html::a(<?= $generator->generateString('新建') ?>, ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote']);
    }

    /**
     * @return string
     */
    protected function getCreateMoreButton()
    {
        return Html::a(<?= $generator->generateString('新建更多') ?>, ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote']);
    }
}
