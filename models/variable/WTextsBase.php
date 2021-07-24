<?php

namespace app\models\variable;

use app\models\ModalText;
use app\models\VariableModel;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель текстов для клиентского виджета (общая)
 *
 * @property string $rotateModal Использовать тексты модального окна из списка ротации
 * @property string $rotateModalIds
 * @property string $rotateModalIdsArray
 * @property string $modalTitle Заголовок модального окна
 * @property string $modalDescription Описание (основной текст) модального окна
 * @property string $modalInputPlaceholder Строка-подсказка в поле ввода
 * @property string $modalSubmit Строка кнопки модального окна (кнопки отправки запроса)
 * @property string $modalSending Сообщение об отправке запроса
 * @property string $modalWrongInput Сообщение о неверном формате ввода
 * @property string $error Строка ошибки
 * @property string $success Строка успеха
 *
 * @property \app\models\ModalText[] $modalTexts
 *
 */
class WTextsBase extends VariableModel
{

    public $language;

    public function __construct($config = [])
    {
        if (!isset($config['name'])) {
            $config['name'] = 'w.texts.base';
        }

        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), [
            'rotateModal',
            'rotateModalIds',

            'modalTitle',
            'modalClose',
            'modalDescription',
            'modalNoRule',
            'modalWorkTimeSelect',
            'modalWTSubmit',
            'modalWTSubmitted',
            'modalNotice',
            'modalSupShow',
            'modalSupAvail',
            'modalSupBusy',
            'modalSupHelped',

            'modalInputPlaceholder',
            'modalSubmit',

            'modalStatusInit',
            'modalStatusInputTip',
            'modalStatusInputOk',
            'modalStatusRequest',
            'modalStatusCallMan',
            'modalStatusZeroDef',

            'error',
            'success',
        ]);

        parent::__construct($config);

        $this->addRule([

            'modalTitle',
            'modalClose',
            'modalDescription',
            'modalNoRule',
            'modalWorkTimeSelect',
            'modalWTSubmit',
            'modalWTSubmitted',
            'modalNotice',
            'modalSupShow',
            'modalSupAvail',
            'modalSupBusy',
            'modalSupHelped',
            'modalInputPlaceholder',
            'modalStatusInit',
            'modalStatusInputTip',
            'modalStatusInputOk',
            'modalStatusRequest',
            'modalStatusCallMan',
            'modalStatusZeroDef',

            'error',
            'success',

        ], 'string');

        $this->addRule([
            'rotateModal',
            'rotateModalIds',
        ], 'safe');
        $this->addRule('modalSubmit', 'string', ['max' => 33]);
    }

    public function adminAttributes()
    {
        return ['modalNotice', 'rotateModal', 'rotateModalIds'];
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'rotateModal' => Yii::t('app', 'Rotate modal texts'),
            'rotateModalIds' => Yii::t('app', 'Modal texts to rotate'),

            'modalTitle' => Yii::t('app', 'Title'),
            'modalClose' => Yii::t('app', 'Close'),
            'modalDescription' => Yii::t('app', 'Description'),
            'modalNoRule' => Yii::t('app', 'No rule description'),
            'modalWorkTimeSelect' => Yii::t('app', 'Hours select title'),
            'modalWTSubmit' => Yii::t('app', 'Hours submit'),
            'modalWTSubmitted' => Yii::t('app', 'Hours submitted description'),
            'modalNotice' => Yii::t('app', 'Notice'),

            'modalInputPlaceholder' => Yii::t('app', 'Input placeholder'),
            'modalSubmit' => Yii::t('app', 'Submit'),

            'modalSupShow' => Yii::t('app', 'Show support statistics'),
            'modalSupAvail' => Yii::t('app', 'Support available'),
            'modalSupBusy' => Yii::t('app', 'Support busy'),
            'modalSupHelped' => Yii::t('app', 'Helped today'),

            'modalStatusInit' => Yii::t('app', 'Initial status'),
            'modalStatusInputTip' => Yii::t('app', 'Input tip'),
            'modalStatusInputOk' => Yii::t('app', 'Input ok'),
            'modalStatusRequest' => Yii::t('app', 'Request sent'),
            'modalStatusCallMan' => Yii::t('app', 'Calling manager'),
            'modalStatusZeroDef' => Yii::t('app', 'Default zero'),

            'error' => Yii::t('app', 'Error message'),
            'success' => Yii::t('app', 'Success message'),
        ]);
    }

    public function getValues()
    {
        $result = parent::getValues();
        $result['previewLabel'] = Yii::t('app', 'widget preview');

        $rm = ArrayHelper::getValue($result, 'rotateModal', 'static');
        $rmIds = ArrayHelper::getValue($result, 'rotateModalIds', '');
        $ids = explode(',', $rmIds);
        if ($rm == 'rotate' && strlen($rmIds) > 0 && count($ids) > 0) {
            $text = $this->queryModalTexts()
                ->andWhere(['IN', 'id', $ids])
                ->orderBy('RAND()')
                ->limit(1)
                ->asArray()->one();

            if ($text) {
                $result['modalText'] = $text;
            }
        }
        if (!isset($result['modalText'])) {
            $result['rotateModal'] = 'static';
        }
        return $result;
    }

    /**
     * Возвращает массив текстов доступных для использования в ротации.
     * @return \app\models\ModalText[]|array
     */
    public function getModalTexts()
    {
        return $this->queryModalTexts()->all();
    }

    public function queryModalTexts()
    {
        return ModalText::find()->where(['AND',
            ['language' => $this->language],
            ['OR',
                ['user_id' => null],
                ['user_id' => $this->user_id],
            ],
        ]);
    }

    public function getRotateModalIdsArray()
    {
        return explode(',', $this->rotateModalIds);
    }

    public function setRotateModalIdsArray($value)
    {
        $this->rotateModalIds = implode(',', $value);
    }

}
