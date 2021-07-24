<?php

use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\Variable;

/* @var $this yii\web\View */
/* @var $model */
/* @var $form app\widgets\ActiveForm */

if (!isset($property)) {
    $property = 'variable_id';
}

$query = Variable::find();
if (!Yii::$app->user->identity->isAdmin) {
    $query->where(['user_id' => Yii::$app->user->id])->orWhere(['user_id' => null]);
}
$data = ArrayHelper::map($query->all(), 'id', function($model) {
    $addon = '';
    if (isset($model->user_id)) {
        $addon = ' (' . Yii::t('app', 'Custom');
        if (Yii::$app->user->identity->isAdmin) {
            $addon .= ': ' . $model->user->username;
        }
        $addon .= ')';
    }
    return $model->name . $addon;
});
echo $form->field($model, $property)->widget(Select2::className(), [
    'data' => $data,
    'hideSearch' => true,
    'pluginOptions' => [
        //'placeholder' => Yii::t('app', 'All users'),
        //'allowClear' => true,
    ],
])->label(Yii::t('app', 'Variable'));
