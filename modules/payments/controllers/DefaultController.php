<?php

namespace app\modules\payments\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Class DefaultController
 *
 * @property \app\modules\payments\Module $module
 */
class DefaultController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'add-select'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->redirect('add');
    }

    public function actionAddSelect()
    {
        $methods = ['yandex', 'paypal', 'paymaster'];
        $methods = $this->module->getMethods();
        return $this->render('add-select', compact('methods'));
    }

}
