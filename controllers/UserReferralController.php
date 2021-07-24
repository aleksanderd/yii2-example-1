<?php

namespace app\controllers;

use app\helpers\ModelsHelper;
use app\models\Notification;
use app\models\Payment;
use app\models\Variable;
use Yii;
use app\models\UserReferral;
use app\models\UserReferralSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use flyiing\helpers\FlashHelper;

/**
 * UserReferralController implements the CRUD actions for UserReferral model.
 */
class UserReferralController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['agreement', 'index', 'view', 'update-scheme', 'partner'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete'],
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

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!ArrayHelper::getValue(Yii::$app->params, 'referrals')) {
            throw new NotFoundHttpException();
        }
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        if (!(in_array($action->id, ['agreement', 'partner']) || ModelsHelper::userPartnerAgreed($user))) {
            return $this->redirect(['/user-referral/agreement']);
        }
        return true;
    }

    public function actionPartner()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $referral = UserReferral::find()->where(['user_id' => $user->id])->one();
        if (!$referral) {
            throw new NotFoundHttpException(Yii::t('app', 'You have no referral partner.'));
        }
        $t = ArrayHelper::getValue(Yii::$app->request->post(), 'UserReferral.p_access', false);
        if ($t !== false) {
            $referral->p_access = $t;
            if ($referral->save(false, ['p_access'])) {
                FlashHelper::setFlash('success', Yii::t('app', 'Updated successfully.'));
            } else {
                FlashHelper::setFlash('error', Yii::t('app', 'Updating failed.'));
            }
        }
        return $this->render('partner', compact('referral'));
    }

    public function actionAgreement()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;

        $version = Variable::sGet('s.settings.referralAgreementVersion');
        $agreement = Variable::sGet('s.settings.referralAgreement');

        $post = Yii::$app->request->post();
        $agreed = ArrayHelper::getValue($post, 'agreed', 0);
        if ($agreed) {
            Variable::sSet('u.referralAgreementVersion', $version, $user->id);
            Variable::sSet('u.referralAgreementTimestamp', time(), $user->id);
            Notification::onUser($user, 'partnerNew');
            FlashHelper::setFlash('success', Yii::t('app', 'Partner agreement signed successfully.'));
            return $this->redirect('/referral-url/index');
        }
        return $this->render('agreement', [
            'agreed' => $agreed,
            'version' => $version,
            'agreement' => $agreement,
        ]);
    }

    public function actionUpdateScheme($partner_id, $user_id, $scheme_id)
    {
        $model = $this->findModel($partner_id, $user_id);
        if ($model->isPaid) {
            throw new BadRequestHttpException(Yii::t('app', 'Changing scheme for paid referral is not allowed.'));
        }
        if (!isset($model->schemeLabels()[$scheme_id])) {
            throw new BadRequestHttpException(Yii::t('app', 'Unknown scheme.'));
        }
        $model->scheme = $scheme_id;
        if ($model->save(false, ['scheme'])) {
            if ($model->scheme > UserReferral::SCHEME_CHOICE_REQUIRED) {
                $msg = Yii::t('app', 'Scheme successfully changed to: {scheme}.', ['scheme' => $model->schemeTextPct]);
                $payments = Payment::find()->where([
                    'user_id' => $model->user_id,
                    'status' => Payment::STATUS_COMPLETED,
                ])->orderBy(['at' => SORT_ASC]);
                if ($model->scheme == UserReferral::SCHEME_FIRST_ONLY) {
                    $payments->limit(1);
                } else if ($model->scheme == UserReferral::SCHEME_LIFETIME_LIMITED) {
                    $payments->andWhere(['<', 'at', strtotime('+1 year', $model->user->created_at)]);
                }
                $payments = $payments->all();
                /** @var Payment[] $payments */
                $amount = 0;
                foreach ($payments as $payment) {
                    $amount += $model->applyPayment($payment, false);
                }
                if ($amount > 0 && $model->save(false, ['status', 'paid'])) {
                    $msg .= '<br>' . Yii::t('app', 'You account charged by: ') . Yii::$app->formatter->asCurrency($amount);
                }
                FlashHelper::setFlash('success', $msg);
            } else {
                FlashHelper::setFlash('success', Yii::t('app', 'Scheme select delayed for future.'));
            }
        } else {
            FlashHelper::setFlash('success', Yii::t('app', 'Scheme update failed.'));
        }
        $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Lists all UserReferral models.
     * @return mixed
     */
    public function actionIndex()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;

        $searchModel = new UserReferralSearch();
        if (!$user->isAdmin) {
            $searchModel->partner_id = $user->id;
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single UserReferral model.
     * @param integer $partner_id
     * @param integer $user_id
     * @return mixed
     */
    public function actionView($partner_id, $user_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($partner_id, $user_id),
        ]);
    }

    /**
     * @param UserReferral $model
     * @return string|\yii\web\Response
     */
    public function modelForm(UserReferral $model)
    {
        $post = Yii::$app->request->post();
        if ($model->load($post)) {

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }

            if ($model->save()) {
                return $this->redirect(['view', 'partner_id' => $model->partner_id, 'user_id' => $model->user_id]);
            } else {
                FlashHelper::flashModelErrors($model->getErrors());
            }

        }
        return $this->render('form', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new UserReferral model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserReferral();
        return $this->modelForm($model);
    }

    /**
     * Updates an existing UserReferral model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $partner_id
     * @param integer $user_id
     * @return mixed
     */
    public function actionUpdate($partner_id, $user_id)
    {
        $model = $this->findModel($partner_id, $user_id);
        return $this->modelForm($model);
    }

    /**
     * Deletes an existing UserReferral model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $partner_id
     * @param integer $user_id
     * @return mixed
     */
    public function actionDelete($partner_id, $user_id)
    {
        $this->findModel($partner_id, $user_id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the UserReferral model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $partner_id
     * @param integer $user_id
     * @param array $options
     * @return UserReferral the loaded model
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function findModel($partner_id, $user_id, $options = [])
    {
        if (($model = UserReferral::findOne(['partner_id' => $partner_id, 'user_id' => $user_id])) !== null) {
            /** @var \app\models\UserReferral $model */
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            if ($user->isAdmin || $user->id == $model->partner_id) {
                return Yii::configure($model, $options);
            } else {
                throw new ForbiddenHttpException(Yii::t('app', 'You are not owner of requested referral link.'));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested referral does not exist.'));
        }
    }
}

