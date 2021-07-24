<?php

use yii\db\Schema;
use dektrium\user\migrations\Migration;
use app\models\ClientRule;

class m150816_164923_rule_hours extends Migration
{
    public function up()
    {
        $rules = ClientRule::find()->all();
        foreach ($rules as $rule) {
            /** @var ClientRule $rule */
            $rule->hours = [];
            foreach ($rule->tmWeek as $weekDay) {
                foreach ($rule->tmDay as $dayHour) {
                    $rule->hours[] = sprintf('%d', $weekDay * 24 + $dayHour);
                }
            }
            //print_r($rule->hours);
            $rule->save();
        }
//        $this->dropColumn('{{%client_rule}}', 'tm_week');
//        $this->dropColumn('{{%client_rule}}', 'tm_day');
    }

    public function down()
    {
    }

}
