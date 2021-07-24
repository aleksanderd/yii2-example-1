<?php

namespace app\models\variable;

use app\models\Variable;
use app\models\VariableModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property string[] $languages
 */
class WTexts extends VariableModel
{
    private $_languages = [];

    public function getLanguages()
    {
        return $this->_languages;
    }

    public function __construct($config = [])
    {
        if (!isset($config['name'])) {
            $config['name'] = 'w.texts';
        }
        $this->_languages = [];
        if ($wLanguage = Variable::sGet('w.settings.language', $config)) {
            $this->_languages[] = $wLanguage;
        }
        foreach (['en', 'ru'] as $l) {
            if ($l == $wLanguage) {
                continue;
            }
            $this->_languages[] = $l;
        }
        $config['attributes'] = array_merge(ArrayHelper::getValue($config, 'attributes', []), $this->_languages);
        parent::__construct($config);
        $this->addRule($this->_languages, 'safe');
        foreach ($this->_languages as $l) {
            $className = '\\app\\models\\variable\\WTexts' . ucfirst($l);
            $this->{$l} = new $className([
                'user_id' => $this->user_id,
                'site_id' => $this->site_id,
                'page_id' => $this->page_id,
            ]);
        }
    }

    public function load($data, $formName = null)
    {
        $result = false;
        foreach ($this->_languages as $l) {
            /** @var \app\models\variable\WTextsBase $model */
            $model = $this->{$l};
            $result |= $model->load($data);

            if (is_array($rmIds = ArrayHelper::getValue($model, 'rotateModalIds', ''))) {
                $model->rotateModalIds = implode(',', $rmIds);
            }
        }
        return $result || parent::load($data, $formName);
    }


    public function attributeLabels()
    {
        $result = [];
        foreach ($this->_languages as $l) {
            $result[$l] = Yii::t('app', 'Texts ('. $l .')');
        }
        return $result;
    }
}
