<?php

namespace app\widgets;

use Yii;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

class SelectUserFile extends Select2 {

    public function __construct($config = [])
    {
        if (!isset($config['data'])) {
            /** @var \app\models\User $user */
            $user = Yii::$app->user->identity;
            $userPath = $user->getFilesPath();
            $path = Yii::getAlias('@webroot/public/' . $userPath);
            $baseUrl = Yii::$app->request->hostInfo . Yii::getAlias('@web/public/' . $userPath);
            $mimeRegex = ArrayHelper::remove($config, 'mimeRegex', false);

            $data = ['none' => Yii::t('app', 'None, speech the text')];
            $scan = scandir($path);
            foreach ($scan as $name) {
                if (substr($name, 0, 1) == '.') {
                    continue;
                }
                if (!($mimeRegex === false || preg_match($mimeRegex, mime_content_type($path .'/'. $name)))) {
                    continue;
                }
                $data[$baseUrl .'/'. $name] = $name;
            }

            $config['data'] = $data;
            if (!isset($config['hideSearch'])) {
                $config['hideSearch'] = true;
            }
        }
        parent::__construct($config);
    }

}
