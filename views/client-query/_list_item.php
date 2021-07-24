<?php

use flyiing\helpers\Html;

/* @var $model \app\models\ClientQuery */

echo Html::tag('span', $model->id);
echo Html::tag('span', $model->getCalls()->count());
