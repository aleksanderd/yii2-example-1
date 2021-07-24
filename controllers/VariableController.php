<?php

namespace app\controllers;

use app\models\variable\VariableKey;
use Yii;
use app\models\Variable;
use app\models\VariableSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use flyiing\helpers\FlashHelper;

/**
 * VariableController implements the CRUD actions for Variable model.
 */
class VariableController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['view', 'form', 'remote-set'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'view', 'update', 'delete', 'form'],
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            /** @var \app\models\User $user */
                            $user = Yii::$app->user->identity;
                            return $user->isAdmin;
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Устанавливает значение переменной. Для использования в запросах ajax.
     *
     * @param string $name Имя переменной
     * @param string $value Значение переменной
     * @param null $user_id
     * @param null $site_id
     * @param null $page_id
     * @return array|bool
     * @throws ForbiddenHttpException
     */
    public function actionRemoteSet($name, $value, $user_id = null, $site_id = null, $page_id = null)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if (!$user->checkSubject($user_id)) {
            throw new ForbiddenHttpException(Yii::t('app', 'The user is not subject for your'));
        }
        return Variable::sSet($name, $value, $user_id, $site_id, $page_id);
    }

    /**
     * Lists all Variable models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new VariableSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Variable model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @param Variable $model
     * @return string|Response
     * @throws ForbiddenHttpException
     */
    public function modelForm(Variable $model)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $post = Yii::$app->request->post();
        if ($model->load($post)) {

            if (!$user->checkSubject($model->user_id)) {
                throw new ForbiddenHttpException(Yii::t('app', 'The user is not subject for your'));
            }

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                FlashHelper::flashModelErrors($model->getErrors());
            }

        }
        return $this->render('form', [
            'model' => $model,
        ]);
    }

    public function actionForm($name, $user_id = null, $site_id = null, $page_id = null)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if (!isset($user_id)) {
            $user_id = $user->id;
        }

        $keyProps = ['user_id', 'site_id', 'page_id'];
        $keyModel = new VariableKey(compact($keyProps));

        $post = Yii::$app->request->post();
        $action = ArrayHelper::getValue($post, '_action', 'save');
        if ($keyModel->load($post)) {
            foreach ($keyProps as $p) {
                if (isset($keyModel->{$p})) {
                    ${$p} = $keyModel->{$p};
                }
            }
        }
        if (!$user->checkSubject($user_id)) {
            throw new ForbiddenHttpException(Yii::t('app', 'The user is not subject for your'));
        }
        if (!$user->isAdmin) {
            $nPrefix = substr($name, 0, 2);
            if ($nPrefix == 's-') {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not allowed to access this form.'));
            }
        }

        $modelClass = '\\app\\models\\variable\\' . Inflector::id2camel($name);
        if (!class_exists($modelClass)) {
            throw new NotFoundHttpException(Yii::t('app', 'Requested variable model not found.'));
        }
        /** @var \app\models\VariableModel $model */
        $model = new $modelClass(compact($keyProps));

        if ($action === 'save' && $model->load($post)) {

            foreach ($keyProps as $p) {
                if ($keyModel->$p != $model->$p) {
                    throw new BadRequestHttpException(Yii::t('app', 'Key IDs mismatch. Select needed user/site/page and click Load, then make changes and click Save.'));
                }
            }

            if (!$user->isAdmin) {
                foreach ($model->adminAttributes() as $attr) {
                    $model->{$attr} = null;
                }
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Values has been updated.'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Values updating failed.'));
            }
        }

        $view = 'forms/' . $name;
        if (!is_readable($this->viewPath .'/'. $view .'.php')) {
            // TODO универсальную форму
            throw new NotFoundHttpException(Yii::t('app', 'Requested variable view not found.'));
        }
        $viewParams = compact('model', 'name', 'keyModel');
        if (Yii::$app->request->isPjax) {
            return $this->renderAjax('variable-form', $viewParams);
        } else {
            return $this->render('variable-form', $viewParams);
        }
    }

    /**
     * Creates a new Variable model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Variable([
            'user_id' => Yii::$app->user->id,
            'type_id' => 0,
        ]);
        return $this->modelForm($model);
    }

    /**
     * Updates an existing Variable model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        return $this->modelForm($model);
    }

    /**
     * Deletes an existing Variable model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Variable model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return Variable the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        if (($model = Variable::findOne($id)) !== null) {
            /** @var \app\models\ClientSite $model */
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->checkSubject($model->user_id)) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested variable.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested variable does not exist.'));
        }
    }
}
