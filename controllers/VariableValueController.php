<?php

namespace app\controllers;

use Yii;
use app\models\VariableValue;
use app\models\VariableValueSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use flyiing\helpers\FlashHelper;

/**
 * VariableValueController implements the CRUD actions for VariableValue model.
 */
class VariableValueController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'create', 'view', 'update', 'delete'],
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
     * Lists all VariableValue models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new VariableValueSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single VariableValue model.
     * @param integer $variable_id
     * @param integer $user_id
     * @param integer $site_id
     * @param integer $page_id
     * @return mixed
     */
    public function actionView($variable_id, $user_id = null, $site_id = null, $page_id = null)
    {
        return $this->render('view', [
            'model' => $this->findModel($variable_id, $user_id, $site_id, $page_id),
        ]);
    }

    /**
     * @param VariableValue $model
     * @return string|Response
     * @throws ForbiddenHttpException
     */
    public function modelForm(VariableValue $model)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $post = Yii::$app->request->post();
        if ($model->load($post)) {

            foreach (['user_id', 'site_id', 'page_id'] as $prop) {
                $val = intval(ArrayHelper::getValue($post, 'VariableValue.' . $prop, null));
                if ($val > 0) {
                    $model->{$prop} = $val;
                } else {
                    $model->{$prop} = null;
                }
            }

            if (!$user->checkSubject($model->user_id)) {
                throw new ForbiddenHttpException(Yii::t('app', 'The user is not subject for your'));
            }

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }

            if ($model->save()) {
                return $this->redirect(array_merge(['view'], $model->getPrimaryKey(true)));
            } else {
                FlashHelper::flashModelErrors($model->getErrors());
            }

        }
        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new VariableValue model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new VariableValue([
            'user_id' => Yii::$app->user->id,
        ]);
        return $this->modelForm($model);
    }

    /**
     * Updates an existing VariableValue model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $variable_id
     * @param integer $user_id
     * @param integer $site_id
     * @param integer $page_id
     * @return mixed
     */
    public function actionUpdate($variable_id, $user_id = null, $site_id = null, $page_id = null)
    {
        $model = $this->findModel($variable_id, $user_id, $site_id, $page_id);
        return $this->modelForm($model);
    }

    /**
     * Deletes an existing VariableValue model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $variable_id
     * @param integer $user_id
     * @param integer $site_id
     * @param integer $page_id
     * @return mixed
     */
    public function actionDelete($variable_id, $user_id = null, $site_id = null, $page_id = null)
    {
        $this->findModel($variable_id, $user_id, $site_id, $page_id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the VariableValue model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $variable_id
     * @param integer $user_id
     * @param integer $site_id
     * @param integer $page_id
     * @param array $options
     * @return VariableValue the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($variable_id, $user_id = null, $site_id = null, $page_id = null, $options = [])
    {
        $model = VariableValue::findOne([
            'variable_id' => $variable_id,
            'user_id' => $user_id,
            'site_id' => $site_id,
            'page_id' => $page_id
        ]);
        if ($model !== null) {
            /** @var \app\models\VariableValue $model */
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->checkSubject($model->user_id)) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested variable value.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested variable value does not exist.'));
        }
    }
}
