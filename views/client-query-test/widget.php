<?php

/* @var $this yii\web\View */

\app\widgets\cbw\CBWAsset::register($this);

//echo Html::tag('div', '111', ['id' => 'cbButton']);
$this->registerJs('jQuery().cbWidget({user_id: 1}).cbWidget("showModal");');

for ($i = 0; $i < 77; $i++) {
    echo '<hr>' . $i;
}
