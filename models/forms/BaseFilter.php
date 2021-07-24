<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;

class BaseFilter extends Model {

    /** @var integer */
    public $user_id;

    /** @var integer */
    public $site_id;

    /** @var integer */
    public $page_id;

    public function rules()
    {
        return [
            [['user_id', 'site_id', 'page_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User'),
            'site_id' => Yii::t('app', 'Website'),
            'page_id' => Yii::t('app', 'Page'),
        ];
    }

}
