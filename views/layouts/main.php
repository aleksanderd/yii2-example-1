<?php
use flyiing\helpers\Html;
use flyiing\widgets\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use yii\helpers\ArrayHelper;
use flyiing\helpers\Icon;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
Icon::register($this);

$this->beginPage();

echo '<!DOCTYPE html>' . PHP_EOL;
echo Html::beginTag('html', ['lang' => Yii::$app->language]);
echo Html::beginTag('head');
echo Html::tag('meta', '', ['charset' => Yii::$app->charset]);
echo Html::tag('meta', '', [
    'name' => 'viewport',
    'content' => 'width=device-width, initial-scale=1',
]);
echo Html::csrfMetaTags();
echo Html::tag('title', Html::encode($this->title));
$this->head();
Html::endTag('head');

echo Html::beginTag('body');
$this->beginBody();

echo $this->render('//layouts/counters');

echo Html::beginTag('div', ['class' => 'wrap']);

NavBar::begin([
    'brandLabel' => 'GMCF',
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => 'navbar-default navbar-fixed-top',
    ],
]);

$menu = require_once(Yii::getAlias('@app/config/menu.php'));

echo Nav::widget([
    'encodeLabels' => false,
    'options' => ['class' => 'navbar-nav'],
    'items' => $menu['main'],
]);
echo Nav::widget([
    'encodeLabels' => false,
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => [$menu['userItem']],
]);
NavBar::end();

echo Html::beginTag('div', ['class' => 'container']);

if (isset($this->params['breadcrumbs'])) {
    echo Breadcrumbs::widget([
        'encodeLabels' => false,
        'homeLink' => [
            'label' => Html::icon('home') . Yii::t('app', 'Home'),
            'url' => Yii::$app->homeUrl,
        ],
        'links' => $this->params['breadcrumbs'],
    ]);
}

$pageHeading = '';
if ($actions = ArrayHelper::getValue($this->params, 'actions')) {
    if (is_array($actions)) {
        $actions = Html::actions($actions, true);
    }
    $pageHeading .= Html::tag('div', $actions, ['class' => 'pull-right']);
}
$pageHeading .= Html::tag('h2', $this->title);
echo Html::tag('div', $pageHeading, ['class' => 'page-heading']);

echo $content;

echo Html::endTag('div'); // class=container
echo Html::endTag('div'); // class=wrap

echo Html::beginTag('footer', ['class' => 'footer']);
echo Html::beginTag('div', ['class' => 'container']);

echo Html::tag('p', '&copy; gmcf.lo', ['class' => 'pull-right']);

echo Html::endTag('div'); // class=container
echo Html::endTag('footer');

$this->endBody();
echo Html::endTag('body');
echo Html::endTag('html');
$this->endPage();
