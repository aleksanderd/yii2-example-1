<?php

namespace app\widgets;

use app\models\Variable;
use app\widgets\hint\HintWidget;
use Yii;
use yii\helpers\Url;

class ActiveField extends \flyiing\widgets\ActiveField {

    public function render($content = null)
    {
        if ($content === null) {
            if (!isset($this->parts['{hint}'])) {
                $hint = HintWidget::widget([
                    'message' => '#' . $this->model->formName() .'.'. $this->attribute .'.hint',
                ]);
                if (strlen($hint) > 0) {
                    $this->hint($hint);
                }
            }
        }
        return parent::render($content);
    }

}
