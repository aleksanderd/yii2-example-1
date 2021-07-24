<?php

namespace app\base;

trait tplModel {

    public function tplPlaceholders($prefix = '')
    {
        /** @var \yii\base\Model $this */
        $result = [];
        foreach($this->getAttributes() as $name => $value) {
            $result['{' . $prefix . $name . '}'] = $value;
        }
        return $result;
    }

    public static function tplDatetimePlaceholders($timestamp = null, $timezone = null, $prefix = 'datetime')
    {
        if (!isset($timestamp)) {
            $timestamp = time();
        }
        if (!isset($timezone)) {
            $timezone = \Yii::$app->timeZone;
        }
        $result = ['{timezone}' => $timezone];
        $tz = new \DateTimeZone($timezone);
        $tzUTC = new \DateTimeZone('UTC');

        $dt = new \DateTime('@' . $timestamp, $tzUTC);
        $result['{'.$prefix.'.utc}'] = $dt->format('Y-m-d H:i:s');
        $dt->setTimezone($tz);
        $result['{'.$prefix.'}'] = $dt->format('Y-m-d H:i:s');

        return $result;
    }

}