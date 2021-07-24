<?php

namespace app\controllers\user;

use Yii;
use app\models\variable\USettings;
use dektrium\user\controllers\SettingsController as BaseController;

class SettingsController extends BaseController
{

    public function behaviors()
    {
        $result = parent::behaviors();
        $result['access']['rules'][0]['actions'][] = 'settings';
        return $result;
    }

    public function actionSettings()
    {
        $model = new USettings(['user_id' => Yii::$app->user->id]);
        $this->performAjaxValidation($model);
        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            $model->user_id = Yii::$app->user->id;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Your settings has been updated'));
            }
        }
        if (isset($model->language)) {
            Yii::$app->language = $model->language;
        }
        return $this->render('settings', compact('model'));
    }

}
