<?php

use yii\helpers\ArrayHelper;

/* @var $this \yii\web\View */
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;

if (!($gaId = ArrayHelper::getValue(Yii::$app->params, 'googleAnalytics.id'))) {
    return;
}

?>

<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', '<?= $gaId ?>', 'auto');
<?php if ($user) {
    printf("ga('set', '&uid', '%s');\n", $user->username);
} ?>
ga('send', 'pageview');

</script>
