<?php

namespace app\controllers;

use app\models\SMessage;
use Yii;
use app\models\STicket;
use app\models\STicketSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use flyiing\helpers\FlashHelper;

/**
 * STicketController implements the CRUD actions for STicket model.
 */
class STicketController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'reply'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update', 'delete'],
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            /** @var \app\models\User $user */
                            $user = Yii::$app->user->identity;
                            return $user && $user->isAdmin;
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
     * Lists all STicket models.
     * @return mixed
     */
    public function actionIndex()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $searchModel = new STicketSearch();
        $params = Yii::$app->request->queryParams;
        if (!$user->isAdmin) {
            $params['STicketSearch']['user_id'] = $user->id;
        }
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single STicket model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionReply($id)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $ticket = $this->findModel($id);
        $post = Yii::$app->request->post();
        $close = ArrayHelper::getValue($post, 'close', false);
        $message = new SMessage();
        if ($message->load($post)) {
            $message->user_id = $user->id;
            $message->ticket_id = $ticket->id;
            if ($message->save()) {

                $ticket->status = $close === false ?
                    ($user->isAdmin ? STicket::STATUS_REPLIED : STicket::STATUS_OPEN) : STicket::STATUS_CLOSED;
                $ticket->save(false, ['status', 'updated_at']);

                FlashHelper::setFlash('success', Yii::t('app', 'Message added successfully.'));
                return $this->redirect(['view', 'id' => $id, '#' => 's-message-' . $message->id]);

            } else {
                FlashHelper::flashModelErrors($message->getErrors());
            }
//            return $this->render('view_reply', ['model' => $message]);
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * @param STicket $model
     * @return string|Response
     * @throws ForbiddenHttpException
     */
    public function modelForm(STicket $model)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $post = Yii::$app->request->post();
        if ($model->load($post)) {

            if ($model->user_id != $user->id && !$user->isAdmin) {
                throw new ForbiddenHttpException(Yii::t('app', 'User mismatch.'));
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

    /**
     * Creates a new STicket model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $model = new STicket([
            'user_id' => $user->id,
            'scenario' => 'create',
        ]);
        return $this->modelForm($model);
    }

    /**
     * Updates an existing STicket model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//        return $this->modelForm($model);
//    }

    /**
     * Deletes an existing STicket model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
//    public function actionDelete($id)
//    {
//        $this->findModel($id)->delete();
//        return $this->redirect(['index']);
//    }

    /**
     * Finds the STicket model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $options
     * @return STicket the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        if (($model = STicket::findOne($id)) !== null) {
            /** @var \app\models\STicket $model */
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->checkSubject($model->user_id)) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested support ticket.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested support ticket does not exist.'));
        }
    }

}
