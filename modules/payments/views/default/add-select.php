<?php

use app\widgets\hint\HintWidget;
use flyiing\helpers\Html;
use flyiing\widgets\AlertFlash;
use app\modules\payments\LogosAsset;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $methods array */

$logosBundle = LogosAsset::register($this);
$logosPrefix = $logosBundle->baseUrl;

$css = <<<CSS
.payment-method-select img.main-logo {
    max-width: 200px;
}
.payment-method-select img.paymaster-logo {
    max-width: 100px;
    padding: 5px;
    margin: 3px;
}
.payment-method-select .method {
    text-align: center;
    padding: 15px;
}
.payment-method-select .method td {
    padding: 5px;
    vertical-align: top;
}
.payment-method-select .link {
    width: 300px;
    text-align: left;
}
.payment-method-select .desc {
    width: 100%;
    text-align: left;
}
.payment-method-select .hint-widget {
    background: transparent;
}
CSS;

$this->registerCss($css);

$this->title = Yii::t('app', 'Select pay method');
$this->params['breadcrumbs'][] = Html::icon(Yii::$app->currencyCode) . $this->title;

echo HintWidget::widget(['message' => '#PaymentsAddSelect.hint']);
echo '<div class="payment-method-select">' . PHP_EOL;
echo AlertFlash::widget();

//echo Html::tag('h2', Yii::t('app', 'Select payment method'));

foreach ($methods as $method) {
    $name = Yii::t('app', $method['name']);
    $url = $method['url'];
    $imgSrc = $logosPrefix .'/'. ArrayHelper::getValue($method, 'logo', strtolower($name) . '.png');
    $img = Html::img($imgSrc, ['class' => 'img-thumbnail main-logo']);
    $link = Html::a($img, $url);
    $desc = HintWidget::widget([
        'showButton' => false,
        'showClose' => false,
        'hidden' => false,
        'message' => '#' . $method['name'] . '.description',
    ]);
    $content = "<table class='method'><tr><td class='link'>$link</td><td class='desc'>$desc</td></tr></table>";
    if ($name == 'Paymaster') {
        $content .= '<br/>' . $this->render('_paymaster_logos') . '<br/>';
    }
    $content .= '<br/>' . Html::a(Yii::t('app', 'Pay with') .' '. $name, $url, ['class' => 'btn btn-success btn-block']);
    echo Html::beginTag('div', ['class' => 'panel panel-default']);
//    echo Html::tag('div', Html::a($name, $url), ['class' => 'panel-heading']);
    echo Html::tag('div', $content, ['class' => 'panel-body']);
    echo Html::endTag('div');
}

echo '</div>' . PHP_EOL;

