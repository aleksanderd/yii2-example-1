<?php

namespace flyiing\translation\controllers;

use flyiing\helpers\FlashHelper;
use flyiing\translation\models\TMessage;
use flyiing\translation\models\TMessageSearch;
use flyiing\translation\models\TSourceMessage;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class MessageController extends Controller
{

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
                        'actions' => ['index', 'create', 'update', 'delete', 'view', 'edit'],
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->isAdmin;
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new TMessageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id, $language)
    {
        return $this->render('view', [
            'model' => $this->findModel($id, $language)
        ]);
    }

    public function modelForm(TMessage $model)
    {
        $post = Yii::$app->request->post();
        if ($model->load($post)) {

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($model);
            }

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id, 'language' => $model->language]);
            } else {
                FlashHelper::flashModelErrors($model->getErrors());
            }

        }
        return $this->render('form', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        return $this->modelForm(new TMessage());
    }

    public function actionUpdate($id, $language)
    {
        return $this->modelForm($this->findModel($id, $language));
    }

    public function actionDelete($id, $language)
    {
        $this->findModel($id, $language)->delete();
        return $this->redirect(['index']);
    }

    public function actionEdit($category, $message)
    {
        $source = TSourceMessage::findOrCreate($category, $message);
        /** @var \flyiing\translation\Module $module */
        $module = Yii::$app->getModule('translation');
        $messages = [];
        foreach ($module->languages as $language) {
            $messages[] = TMessage::findOrCreate($source, $language);
        }
        if (TMessage::loadMultiple($messages, Yii::$app->request->post()) && TMessage::validateMultiple($messages)) {
            $success = 0;
            $errors = 0;
            foreach ($messages as $m) {
                /** @var \flyiing\translation\models\TMessage $m */
                if ($m->save(false)) {
                    $success++;
                } else {
                    $errors++;
                }
            }
            if ($success > 0) {
                FlashHelper::setFlash('success', Yii::t('app', 'Updated {success} translations.', compact('success')));
            }
            if ($errors > 0) {
                FlashHelper::setFlash('success', Yii::t('app', '{success} translations not saved.', compact('success')));
            }
        }
        $pms = Yii::$app->request->post('TMessage', []);
//        foreach ($pms as $pm) {
//            $m = TMessage::findOrCreate($pm['id'], $pm['language'], $pm['translation'], true);
//            if ($m->translation != $pm['translation']) {
//                FlashHelper::setFlash('error', Yii::t('app', 'Translations saving failed.'));
//            }
//            FlashHelper::setFlash('success', Yii::t('app', 'Translations saved successfully.'));
//        }
        return $this->render('edit', compact('source', 'messages'));
    }

    /**
     * @param $id
     * @param $language
     * @return TMessage
     * @throws NotFoundHttpException
     */
    protected function findModel($id, $language)
    {
        if (($model = TMessage::findOne(compact('id', 'language'))) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'Translation message not found.'));
        }

    }
}