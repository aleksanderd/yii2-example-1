<?php
// Для разных тестов
namespace app\controllers;

use app\models\ClientSite;
use app\models\Notification;
use flyiing\helpers\FlashHelper;
use flyiing\helpers\Html;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class TestController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
//                        'actions' => ['time', 'test', 'voxlog', 's3', 'page'],
                        'actions' => [],
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->isAdmin;
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionPage()
    {
        /** @var ClientSite $site */
        $site = ClientSite::findOne(1);
        if ($page = $site->findPageByUrl('http://gmcf.lo/demo/simple.html?p=v&p1=v1')) {
            var_dump($page);
        } else {
            echo '404';
        }
    }

    public function actionTest()
    {
        $post = Yii::$app->request->post();
        $model = new Notification([
            'user_id' => 2,
            'site_id' => 1,
            'query_id' => null,
            'type' => Notification::TYPE_SMS,
            'at' => time(),
            'subject' => 'Test message',
            'body' => 'Test body' . time(),
            'from' => '+79519186029',
            'to' => '+79519186029',
        ]);
        if ($model->load($post)) {
            $model->send();
            FlashHelper::setFlash('success', 'Отправлено, статус ' . $model->description);
        };

        return $this->render('form', ['model' => $model]);
    }

    public function actionVoxlog()
    {
        $contents = json_decode(
            file_get_contents('https://api.voximplant.com/platform_api/GetCallHistory/?account_id=106763&api_key=14dca8a7-1656-4893-bd35-64e2ba50cfdf&with_calls=true&desc_order=true')
        );
        echo "<pre>";
        print_r($contents);
        echo "</pre>";

    }

    public function actionS3()
    {

        $recordUrl = 'http://www-ru-03-4.voximplant.com/records/2015/06/07/a0c4ab9d12a81402.1433683210.828846.mp3?record_id=hNK18yuTRNiWfrKqqSAVtiJdZpvNLEizmMZ3IC0Jrlo';
        $storage = \Yii::$app->storage;
        if ($p = strpos($recordUrl, '?')) {
            $recordUrl = substr($recordUrl, 0, $p);
        }
        $url = $storage->save($recordUrl, 'records/' . 'test' . '/' . basename($recordUrl));
        echo $url;
    }

}
