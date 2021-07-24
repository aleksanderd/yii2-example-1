<?php

namespace app\controllers;

use app\helpers\DataHelper;
use flyiing\helpers\FlashHelper;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

class FileController extends Controller {

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'manager', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->redirect(['manager']);
    }

    public function actionManager()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;

        $uploadedFiles = UploadedFile::getInstancesByName('files2upload');
        $userPath = $user->getFilesPath();
        $path = Yii::getAlias('@webroot/public/' . $userPath);
        $baseUrl = Yii::getAlias('@web/public/' . $userPath);
        if (!FileHelper::createDirectory($path)) {
            throw new ServerErrorHttpException(Yii::t('app', 'Can not create user public directory.'));
        }
        $maxSize = 2*1024*1024;
        foreach ($uploadedFiles as $k => $file) {
            $tOpts = ['filename' => $file->name];
            if ($file->size > $maxSize) {
                FlashHelper::addFlash('error', Yii::t('app', 'File "{filename}" is too big. Maximum allowed size is {size}.',
                    array_merge($tOpts, ['size' => DataHelper::formatBytes($maxSize)])));
                continue;
            }
            if ($file->saveAs($path . DIRECTORY_SEPARATOR . $file->name)) {
                FlashHelper::addFlash('success', Yii::t('app', 'File "{filename}" uploaded successfully.', $tOpts));
            } else {
                FlashHelper::addFlash('error', Yii::t('app', 'File "{filename}" uploading failed.', $tOpts));
            }
        }

        $files = [];
        $scan = scandir($path);
        foreach ($scan as $name) {
            if (substr($name, 0, 1) == '.') {
                continue;
            }
            $filePath = $path . DIRECTORY_SEPARATOR . $name;
            $files[] = [
                'name' => $name,
                'size' => filesize($filePath),
                'mime' => mime_content_type($filePath),
            ];
        }
        return $this->render('manager', compact('files', 'baseUrl'));
    }

    public function actionDelete($name)
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;

        $filePath = Yii::getAlias('@webroot/public/' . $user->getFilesPath() .'/'. $name);
        $tOpts = ['filename' => $name];
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException(Yii::t('app', 'File "{filename}" does not exists.', $tOpts));
        }
        if (unlink($filePath)) {
            FlashHelper::addFlash('success', Yii::t('app', 'File "{filename}" deleted.', $tOpts));
        } else {
            FlashHelper::addFlash('error', Yii::t('app', 'Can not delete file "{filename}".', $tOpts));
        }

        return $this->redirect(['index']);
    }

}
