<?php

namespace app\helpers;

use Curl\Curl;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Класс VoxImplant определяет некоторые функции для общения с сервисом *VoxImplant*
 */
class VoxImplant extends Object {

    /** @var string Базовый адрес для соединений с *VoxImplant* */
    public $baseUrl = 'https://api.voximplant.com/platform_api/';

    /** @var array Параметры */
    public $params = [];

    /** @var string Адрес для общения с текущей сессий сценария */
    public $media_session_access_url = null;

    /**
     * @var Curl
     */
    protected $_curl = null;

    public function init()
    {
        parent::init();
        $this->params = ArrayHelper::merge(
            ArrayHelper::getValue(\Yii::$app->params, 'voxImplant.params', []),
            $this->params
        );
        $this->_curl = new Curl();
    }

    /**
     * Выполнение функции API сервиса *VoxImplant*
     * @param string $fName Имя функции
     * @param array $params Параметры
     * @param bool $debug Переменная для сохранения отладочной инфы, если не нужно - false
     * @return string Ответ от сервиса
     */
    public function exec($fName, $params = [], &$debug = false)
    {
        $url = $this->baseUrl . $fName . '/';
        $p = ArrayHelper::merge($this->params, $params);
        foreach ($p as $pk => $pv) {
            if (is_array($pv)) {
                $p[$pk] = json_encode($pv);
            }
        }
        if (is_array($debug)) {
            if (sizeof($params) > 0) {
                $debug['params'] = $params;
            }
            $this->_curl->setURL($url, $p);
            $debug['url'] = $this->_curl->url;
        }
        return $this->_curl->post($url, $p);
    }

    public function startScenarios($params = [], &$debug = false)
    {
        if (!($response = $this->exec('StartScenarios', $params, $debug))) {
            return false;
        }

        if (isset($response->result) && $response->result == 1 && isset($response->media_session_access_url)) {
            $this->media_session_access_url = $response->media_session_access_url;
        }
        return $response;
    }

    public function sessionExec($params = [], &$debug = false)
    {
        if (!($url = $this->media_session_access_url)) {
            return false;
        }
        if (is_array($debug)) {
            if (sizeof($params) > 0) {
                $debug['params'] = $params;
            }
            $this->_curl->setURL($url, $params);
            $debug['url'] = $this->_curl->url;
        }
        $res = $this->_curl->post($this->media_session_access_url, $params);
        return json_decode(json_encode($res), true);
    }

}