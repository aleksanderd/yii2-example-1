<?php

namespace app\helpers;

use app\models\ClientSite;
use app\models\Notification;
use app\models\Variable;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

class DataHelper {

    const PERIOD_MINUTE = 60;
    const PERIOD_HOUR = 3600;
    const PERIOD_DAY = 86400;
    const PERIOD_MONTH = 2592000;
    const PERIOD_YEAR = 31104000;

    private static $_bots;

    /**
     * Конвертирует массив содержащий номера битов в число в котором эти биты 1, остальные - 0.
     * Наример: [1, 3] => 9 или [0, 2] => 5.
     * Обратная функция к bitsToArray().
     * @param int[] $array Массив номеров установленных битов
     * @param int $maxBit Максимальный номер бита в числе, 0..31
     * @return int Целое число, битовая маска
     */
    public static function arrayToBits($array, $maxBit = 31)
    {
        if ($maxBit < 0 || $maxBit > 31) {
            throw new InvalidParamException('$maxBit must be in 0..31');
        }
        $result = 0;
        foreach ($array as $bit) {
            $bit = intval($bit);
            if ($bit < 0 || $bit > $maxBit) {
                continue;
            }
            $result |= pow(2, $bit);
        }
        return $result;
    }

    /**
     * Конвертирует целое число в массив содержащий номера битов которые в числе были равны 1.
     * Например, 21 => [0, 2, 4] или 3 => [0, 1].
     * Обратная функция к arrayToBits().
     * @param int $bits Целое число, битовая маска
     * @param int $maxBit Максимальный номер бита в числе, 0..31
     * @return array Массив, содержащий номера установленных битов
     */
    public static function bitsToArray($bits, $maxBit = 31)
    {
        if ($maxBit < 0 || $maxBit > 31) {
            throw new InvalidParamException('$maxBit must be in 0..31');
        }
        $result = [];
        for ($bit = 0; $bit <= $maxBit; $bit++) {
            if ($bits & pow(2, $bit)) {
                $result[] = $bit;
            }
        }
        return $result;
    }

    public static function durationToText($duration)
    {
        $m = floor($duration / 60);
        $s = $duration - $m * 60;
        return sprintf('%02d\'%02d\'\'', $m, $s);
    }

    /**
     * Возвращает имя домена урла (без `www.`)
     *
     * @param $url
     * @return string
     */
    public static function getDomain($url)
    {
        $result = strtolower(parse_url($url, PHP_URL_HOST));
        if (strlen($result) > 4 && substr($result, 0, 4) === 'www.') {
            $result = substr($result, 4);
        }
        return $result;
    }

    public static function isPeriodValid($period)
    {
        return in_array($period, [
            static::PERIOD_MINUTE,
            static::PERIOD_HOUR,
            static::PERIOD_DAY,
            static::PERIOD_MONTH,
            static::PERIOD_YEAR,
        ]);
    }

    public static function truncateDatetime($datetime, $period)
    {
        switch ($period) {
            case static::PERIOD_MINUTE:
                $str = date('Y-m-d H:i:00', $datetime);
                break;
            case static::PERIOD_HOUR:
                $str = date('Y-m-d H:00:00', $datetime);
                break;
            case static::PERIOD_DAY:
                $str = date('Y-m-d 00:00:00', $datetime);
                break;
            case static::PERIOD_MONTH:
                $str = date('Y-m-01 00:00:00', $datetime);
                break;
            case static::PERIOD_YEAR:
                $str = date('Y-01-01 00:00:00', $datetime);
                break;
            default:
                return $datetime;
        }
        return strtotime($str);
    }

    public static function getBotsAgents($forced = false)
    {
        if (!is_array(static::$_bots) || $forced) {
            $filename = Yii::getAlias('@app/config/bots.php');
            static::$_bots = is_readable($filename) ? require($filename) : [];
        }
        return static::$_bots;
    }

    public static function isBotVisit($userAgent, $bots = null)
    {
        if ($bots === null) {
            $bots = static::getBotsAgents();
        }
        foreach ($bots as $bot) {
            if ($bot[0] == '/' && preg_match($bot, $userAgent) || strstr($userAgent, $bot)) {
                return $bot;
            }
        }
        return false;
    }

    /**
     * Пробразует переданный урл к нормальному виду:
     * конвертирует домен Punycode => UTF8 и декодирует с помощью urldecode.
     *
     * @param $url
     * @return string
     */
    public static function normalizeUrl($url)
    {
        if (!strstr($url, '://')) {
            $url = 'http://' . $url;
        }
        if (!($parts = parse_url($url))) {
            return $url;
        }
        $result = isset($parts['scheme']) ? $parts['scheme'] . '://' : 'http://';
        if (isset($parts['user'])) {
            $result .= $parts['user'];
            if (isset($parts['pass'])) {
                $result .= ':' . $parts['pass'];
            }
            $result .= '@';
        }
        $result .= isset($parts['host']) ? idn_to_utf8($parts['host']) : '';
        $result .= isset($parts['path']) ? $parts['path'] : '';
        $result .= isset($parts['query']) ? '?' . $parts['query'] : '';
        return urldecode($result);
    }

    public static function idnUrl($url)
    {
        if (!strstr($url, '://')) {
            $url = 'http://' . $url;
        }
        if (!($parts = parse_url($url))) {
            return $url;
        }
        $result = isset($parts['scheme']) ? $parts['scheme'] . '://' : 'http://';
        if (isset($parts['user'])) {
            $result .= $parts['user'];
            if (isset($parts['pass'])) {
                $result .= ':' . $parts['pass'];
            }
            $result .= '@';
        }
        $result .= isset($parts['host']) ? idn_to_ascii($parts['host']) : '';
        $result .= isset($parts['path']) ? $parts['path'] : '';
        $result .= isset($parts['query']) ? '?' . $parts['query'] : '';
        return $result;
    }

    public static function timezoneFull($timezone)
    {
        $tz = new \DateTimeZone($timezone);
        $offset = $tz->getOffset(new \DateTime())/60/60;
        $offsetText = ($offset < 0 ? '-' : '+') . sprintf('%02d', abs($offset));
        return $offsetText .' '. Yii::t('app', $timezone);
    }

    /**
     * Создаёт файл для блокировок с текущим временем(timestamp), при этом проверяет, если файл уже существует и
     * таймаут не вышел, то возвращает время, оставшееся до конца блокировки. Если всё ок - возвращает true.
     * При ошибке записи - false.
     * @param string $name
     * @param int $timeout
     * @return bool|int
     */
    public static function lock($name, $timeout = 3600)
    {
        $time = time();
        $filename = Yii::getAlias('@app/runtime/' . $name . '.lock');
        if (file_exists($filename)) {
            $lock = intval(file_get_contents($filename));
            if (($time - $lock) < $timeout) {
                return $lock;
            }
            $s = date('Y-m-d H:i:s', $time) .': '. Yii::t('app', 'Lock file created at {lock} removed cos timeout.', [
                'lock' => date('Y-m-d H:i:s', $lock)]) . PHP_EOL;
            file_put_contents($filename .'.timeouts', $s, LOCK_EX|FILE_APPEND);
        }
        return file_put_contents($filename, $time, LOCK_EX) !== false;
    }

    /**
     * Удаляет файл блокировки.
     * @param $name
     * @return bool
     */
    public static function unlock($name)
    {
        return unlink(Yii::getAlias('@app/runtime/' . $name . '.lock'));
    }

    public static function triggersData()
    {
        return [
            'manual' => 0,
            'scrollEnd' => 10,
            'selectText' => 20,
            'mouseExit' => 30,
            'period' => 1000,
        ];
    }

    public static function triggersLabels()
    {
        $data = static::triggersData();
        $result = [];
        foreach ($data as $k => $v) {
            $result[$v] = Yii::t('app', 'tr_' . $k);
        }
        return $result;
    }

    public static function triggerId($value, $reverse = false)
    {
        $data = static::triggersData();
        $default = 0;
        if ($reverse) {
            $data = array_flip($data);
            $default = 'manual';
        }
        return ArrayHelper::getValue($data, $value, $default);
    }

    public static function checkWidgetCode(ClientSite $site)
    {
        try {
            $url = static::idnUrl($site->url);
            $html = file_get_contents($url);
        } catch(\Exception $e) {
            $html = false;
        }

        // .//script[@src='http://mydashboard.gmcf.ru/cli/cbWidgetLoad.js']
        // .//script[contains(text(), 'cbWidgetLoad(')]

        if ($html === false) {
            return ClientSite::CODE_ERROR;
        }

        $p = parse_url($site->url);
        $dir = Yii::getAlias('@app/runtime/check-codes/' . $p['host']);
        FileHelper::createDirectory($dir);
        file_put_contents($dir . '/' . date('y-m-d_His') . '.html', $html);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        $xp = new \DOMXPath($dom);

        $baseUrl = Variable::sGet('s.settings.baseUrl', $site->user_id, $site->id);

        $nodes = $xp->query('.//script[@src="' . $baseUrl . 'cli/cbWidgetLoad.js"]');
        if (!($nodes) || $nodes->length < 1) {
            return ClientSite::CODE_NONE;
        }
        $nodes = $xp->query('.//script[contains(text(), "cbWidgetLoad(")]');
        if (!($nodes) || $nodes->length < 1) {
            return ClientSite::CODE_NONE;
        }


        return ClientSite::CODE_OK;
    }

    public static function checkWidgetCodeSave(ClientSite $site)
    {
        $res = static::checkWidgetCode($site);
        $site->w_checked_at = time();
        $update = ['w_checked_at'];
        if ($site->w_check_result != $res || $site->w_changed_at == 0) {

            if ($res == ClientSite::CODE_NONE && $site->w_check_result == ClientSite::CODE_OK) {
                Notification::onSite($site, 'widgetRemoved');
            }

            $site->w_changed_at = $site->w_checked_at;
            $site->w_check_result = $res;
            $update = array_merge($update, ['w_changed_at', 'w_check_result']);
        }
        $site->save(false, $update);
        return $res;
    }

    public static function parseAcceptLanguages($data)
    {
        $result = [];
        $items = explode(',', $data);
        foreach ($items as $item) {
            $parts = explode(';', $item);
            if (count($parts) > 1) {
                $pp = explode('=', $parts[1]);
                $q = count($pp) > 1 ? floatval($pp[1]) : 1;
            } else {
                $q = 1;
            }
            $result[trim($parts[0])] = $q;
        }
        arsort($result);
        return $result;
    }

    public static function formatBytes($bytes, $precision = 1)
    {
        $units = [
            Yii::t('app', 'b'),
            Yii::t('app', 'Kb'),
            Yii::t('app', 'Mb'),
            Yii::t('app', 'Tb'),
        ];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Возвращает массив списка файлов по заданному относительного пути. В качестве ключей - http урл,
     * в качестве значений - имена файлов.
     * @param string $subPath
     * @param bool $mimeRegex
     * @return array
     */
    public static function filesSelectData($subPath = '/', $mimeRegex = false)
    {
        $result = [];
        $path = Yii::getAlias('@webroot' . $subPath);
        if (!(is_dir($path) && is_readable($path))) {
            return [];
        }
        $baseUrl = Yii::$app->request->hostInfo . Yii::getAlias('@web' . $subPath);
        $scan = scandir($path);
        foreach ($scan as $name) {
            if (substr($name, 0, 1) == '.') {
                continue;
            }
            if (!($mimeRegex === false || preg_match($mimeRegex, mime_content_type($path .'/'. $name)))) {
                continue;
            }
            $result[$baseUrl .'/'. $name] = $name;
        }
        return $result;
    }

}
