<?php

namespace app\controllers;

use app\models\PaymentSearch;
use flyiing\helpers\FlashHelper;
use Yii;
use app\models\Payment;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PaymentController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'complete'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'force-complete', 'stats'],
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            /** @var \app\models\User $user */
                            $user = Yii::$app->user->identity;
                            return $user->isAdmin;
                        },
                    ],
                ],
            ],
        ];
    }

    /**
     * Завершение платежа: созание транзакции по переданному платежу.
     * @param integer $pid Идентификатор платежа
     * @return string
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionComplete($pid)
    {
        $model = $this->findModel($pid);
        if ($model->status !== Payment::STATUS_COMPLETED) {
            throw new BadRequestHttpException(Yii::t('app', 'The payment is not completed.'));
        }
        $transaction = $model->createTransaction();
        if ($transaction->save()) {
            FlashHelper::setFlash('success',
                Yii::t('app', 'Funds added successfully.'));
        } else {
            FlashHelper::flashModelErrors($transaction->errors);
            $transaction = false;
        }
        return $this->render('complete', compact('model', 'transaction'));
    }

    public function actionForceComplete($id)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if (!$user->isAdmin) {
            throw new ForbiddenHttpException();
        }
        $model = $this->findModel($id);
        $model->admin_id = $user->id;
        if ($model->status >= Payment::STATUS_COMPLETED) {
            throw new BadRequestHttpException;
        }
        $model->status = Payment::STATUS_COMPLETED;
        $model->save();
        $this->redirect(['complete', 'pid' => $id]);
    }

    /**
     * @param Payment $model
     * @return string|\yii\web\Response
     */
    public function modelForm(Payment $model)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $post = Yii::$app->request->post();
        if ($model->load($post)) {

            $model->admin_id = $user->id;

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }

            if ($model->save()) {
                return $this->redirect(['complete', 'pid' => $model->id]);
                //return $this->redirect(['view', 'id' => $model->id]);
            } else {
                FlashHelper::flashModelErrors($model->getErrors());
            }

        }
        return $this->render('form', compact('model'));
    }

    /**
     * Creates a new ClientSite model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Payment([
            'user_id' => Yii::$app->user->id,
            'method' => Payment::METHOD_BASIC,
            'status' => Payment::STATUS_COMPLETED,
        ]);
        return $this->modelForm($model);
    }

    /**
     * Updates an existing ClientSite model.
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
     * Lists all Payment models.
     * @return mixed
     */
    public function actionIndex()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $searchModel = new PaymentSearch();
        $params = Yii::$app->request->queryParams;
        if (!$user->isAdmin) {
            $params['PaymentSearch']['user_id'] = $user->id;
        }
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionStats()
    {
        $searchModel = new PaymentSearch();
        $params = Yii::$app->request->queryParams;

        $searchModel->groupBy = PaymentSearch::GROUP_BY_DT_MONTH;
        $searchModel->status = Payment::STATUS_COMPLETED;

        $dataProvider = $searchModel->search($params);



        return $this->render('stats', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Payment model.
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
     * @param $id
     * @param array $options
     * @return \app\models\Payment
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $options = [])
    {
        if (($model = Payment::findOne($id)) !== null) {
            /** @var \app\models\Payment $model */
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->isAdmin || $user->id == $model->user_id) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested payment.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested payment does not exist.'));
        }
    }
}
