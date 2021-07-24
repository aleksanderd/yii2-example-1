<?php

namespace app\modules\payments\components;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class Paymaster extends Component
{

    public function directPost($url, $data = [])
    {
        //$iat = ArrayHelper::remove($data, 'iat', time());
        //$key = ArrayHelper::getValue(Yii::$app->params, 'paymaster.keyDirect');

        $iat = time();
        $key = 'gmcf11paymaster';

        /** @var string $post Тело поста, в виде имя=значение&... Значения urlencoded */
        $post = http_build_query($data);
        $signSrc = '';
        foreach ($data as $pName => $pValue) {
            $signSrc .= $pValue . ';';
            // Или значение параметра кодировать?
            //$signSrc .= urlencode($pValue) . ';';
        }
        $signSrc .= $iat .';'. $key;
        // Второй вариант, подписываем "тело поста", как в сказано доках:
        //$signSrc = $post .';'. $iat .';'. $key;

        $signHash = hash('sha256', $signSrc);
        $sign = base64_encode($signHash);
        // если закодировать подпись, то результат: Произошла ошибка при работе с сайтом, иногда Bad Gateway
        //$sign = urlencode($sign);
        $headers = [
            'type: rest',
            'iat: ' . $iat,
            'sign: ' . $sign,
        ];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        // для посмотреть:
        $content = '<pre>' . print_r(compact('data', 'post', 'signSrc', 'signHash', 'sign', 'headers'), true) . '</pre>';
        if (curl_exec($ch)) {
            $resUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            echo '<hr>' . $content . '<hr>' . $resUrl . '<hr>';

            return true;
        } else {
            echo $url . ': false';
            return false;
        }

    }

}
