<?php

namespace app\commands;

use app\models\Tariff;
use yii\console\Controller;
use yii\helpers\Console;

class TariffController extends Controller {

    public function actionCreateTest()
    {
        $tariffs = [
            [
                'title' => 'Стандартный: 3 месяца',
                'status' => Tariff::STATUS_PUBLIC,
                'desc' => '+82% звонков от посетителей Вашего сайта',
                'price' => 2500,
                'lifetime_measure' => Tariff::LTM_MONTH,
                'lifetime' => 3,
                'minutes' => 300,
                'messages' => 1000,
                'space' => 500,
            ],
            [
                'title' => 'Стандартный: 1 месяц',
                'status' => Tariff::STATUS_PUBLIC,
                'desc' => '+82% звонков от посетителей Вашего сайта',
                'price' => 950,
                'lifetime_measure' => Tariff::LTM_MONTH,
                'lifetime' => 1,
                'minutes' => 100,
                'messages' => 330,
                'space' => 160,
            ],
            [
                'title' => 'Стандартный: 10 дней',
                'status' => Tariff::STATUS_PUBLIC,
                'desc' => '+82% звонков от посетителей Вашего сайта',
                'price' => 100,
                'lifetime_measure' => Tariff::LTM_DAY,
                'lifetime' => 10,
                'minutes' => 10,
                'messages' => 33,
                'space' => 16,
            ],
            [
                'title' => 'Стандартный: 3 дня',
                'status' => Tariff::STATUS_PUBLIC,
                'desc' => '+82% звонков от посетителей Вашего сайта',
                'price' => 50,
                'lifetime_measure' => Tariff::LTM_DAY,
                'lifetime' => 3,
                'minutes' => 5,
                'messages' => 15,
                'space' => 10,
            ],
            [
                'title' => 'Разовый пакет: 1000 минут',
                'desc' => 'Не ограниченный по времени пакет на 100 минут разговора, всё остальное - без ограничений!',
                'status' => Tariff::STATUS_PUBLIC,
                'lifetime' => 0,
                'minutes' => 1000,
                'renewable' => 0,
                'price' => 7500,
            ],
            [
                'title' => 'Разовый пакет: 100 минут',
                'desc' => 'Не ограниченный по времени пакет на 100 минут разговора, всё остальное - без ограничений!',
                'status' => Tariff::STATUS_PUBLIC,
                'lifetime' => 0,
                'minutes' => 100,
                'renewable' => 0,
                'price' => 1100,
            ],
            [
                'title' => 'Разовый пакет: 50 минут',
                'desc' => 'Не ограниченный по времени пакет на 50 минут разговора, всё остальное - без ограничений!',
                'status' => Tariff::STATUS_PUBLIC,
                'lifetime' => 0,
                'minutes' => 50,
                'renewable' => 0,
                'price' => 650,
            ],
            [
                'title' => 'Разовый пакет: 10 минут',
                'desc' => 'Не ограниченный по времени пакет на 10 минут разговора, всё остальное - без ограничений!',
                'status' => Tariff::STATUS_PUBLIC,
                'lifetime' => 0,
                'minutes' => 10,
                'renewable' => 0,
                'price' => 150,
            ],
            [
                'title' => 'Продляемый пакет: 10 минут',
                'desc' => 'Не ограниченный по времени пакет на 10 минут разговора, всё остальное - без ограничений!',
                'status' => Tariff::STATUS_PUBLIC,
                'renewable' => 1,
                'lifetime' => 0,
                'minutes' => 10,
                'price' => 150,
            ],
            [
                'title' => 'Полная халява',
                'status' => Tariff::STATUS_INTERNAL,
                'desc' => 'Совершенно бесплатно! Всё! Вечно! (Для внутреннего использования)',
                'price' => 0,
                'lifetime' => 0,
            ],
        ];
        foreach ($tariffs as $tConfig) {
            $this->stdout('Creating tariff "' . $tConfig['title'] .'": ');
            if ($tariff = Tariff::find()->where($tConfig)->one()) {
                $this->stdout('alredy exists.' . PHP_EOL, Console::FG_YELLOW);
                continue;
            }
            $tariff = new Tariff($tConfig);
            if ($tariff && $tariff->save()) {
                $this->stdout('successful' . PHP_EOL, Console::FG_GREEN);
            } else {
                $this->stdout('failed' . PHP_EOL, Console::FG_GREEN);
            }
        }
    }

}