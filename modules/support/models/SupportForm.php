<?php

namespace app\modules\support\models;

use app\models\Variable;
use flyiing\helpers\FlashHelper;
use flyiing\helpers\Html;
use Yii;
use yii\base\Model;
use yii\web\ServerErrorHttpException;

class SupportForm extends Model
{
    public $user_id;
    public $name;
    public $email;
    public $subject;
    public $message;
    public $url;

    public function attributeLabels()
    {
        return
        [
            'name' => Yii::t('app', Yii::t('app', 'Contact name')),
            'email' => Yii::t('app', Yii::t('app', 'Contact email')),
            'subject' => Yii::t('app', Yii::t('app', 'Subject')),
            'message' => Yii::t('app', Yii::t('app', 'Message')),
            'url' => Yii::t('app', Yii::t('app', 'Source url')),
        ];
    }

    public function rules()
    {

        return [
            [['name', 'email', 'subject', 'message'], 'required'],
            [['name', 'subject', 'message', 'url'], 'string'],
            [['user_id'], 'integer'],
            [['email'], 'email'],
        ];
    }

//    public function upload()
//    {
//        // FIXME
//        // валидацию проверяем в контроллере!
//        // если тут нада, просто return false если модель !isValid
//        if ($this->validate()) {
//
//            $mes = \Yii::$app->mailer->compose('support', [
//                'name' => $this->name,
//                'message' => $this->message,
//                'url' => $this->url]);
//
//            if ($this->file->baseName) {
//                $this->file->saveAs($this->path . $this->file->baseName . '.' . $this->file->extension);
//                $mes->attach($this->path . $this->file->baseName . '.' . $this->file->extension);
//            }
//
//            $mes->setFrom([$this->email => 'Запрос от пользователя ' . $this->name])
//                ->setTo(Variable::sGet('s.settings.supportEmail'))
////                ->setTo('support@jetstd.ru')
//                ->setSubject('Запрос от пользователя ' . $this->name)
//                ->send();
//
//            return true;
//        } else {
//            throw new ServerErrorHttpException('Это, блядь, даже не тестировалось!<br>' .
//                Html::tag('pre', print_r($this->getErrors(), true)));
//            /**
//             * FIXME
//             *
//             * Форма не валидна если юзер залогинен - имя, емэйл и тд нигде не задаются!
//             * То что про файл - ваще блядь для красоты! что за saveAs? что такое file вообще?
//             * Где оно инициализируется? Если даже пройдет валидация, мы получим ошибку на if ($this->file->baseName) {
//             *
//             * И где отправка в ПФ?
//             *
//             */
//            return false;
//        }
//    }

}
