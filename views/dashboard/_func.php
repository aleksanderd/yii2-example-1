<?php

use flyiing\helpers\Html;

/**
 * @param $value
 * @param string $suffix
 * @return string
 */
function renderValue($value, $suffix = '')
{
    if (is_float($value)) {
        $value = sprintf('%.2f', $value);
    }
    return $value . $suffix;
}

/**
 * @param $name
 * @param string $suffix
 * @return string
 */
function renderItem($name, $suffix = '')
{
    global $mLast, $mPrev;
    $label = $mLast->getAttributeLabel($name);
    if (stristr($name, 'cost')) {
        $label .= ' ' . Html::icon(Yii::$app->currencyCode);
    }

    $content = '<tr><th>' . $label . '</th><td>';

    if (isset($mPrev)) {
        $content .= renderValue($mPrev->{$name}, $suffix);
        $content .= '</td><td>';
    }

    $content .= renderValue($mLast->{$name}, $suffix);

    if (isset($mPrev)) {
        if (($change = $mLast->{$name} - $mPrev->{$name}) > 0) {
            $color = stristr($name, 'fail') === false ?  'green' : 'red';
            $sign = Html::icon('long-arrow-up');
        } else if ($change < 0) {
            $color = stristr($name, 'fail') === false ?  'red' : 'green';
            $sign = Html::icon('long-arrow-down');
        } else {
            $color = 'grey';
            $sign = '=';
        }
        if ($mPrev->{$name} > 0) {
            $pct = 100 * abs($change) / $mPrev->{$name};
        } else if ($mLast->{$name}) {
            $pct = 100;
            $sign = Html::icon('rocket');
        } else {
            $pct = '-';
            $sign = '';
        }
        $content .= '</td><td style="color: ' . $color .'">' . $sign;
        if ($sign != '=' && $pct != '-') {
            $content .= renderValue($pct, '%');
        }
    }

    $content .= '</td></tr>';
    return $content;
}
