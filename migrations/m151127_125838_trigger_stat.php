<?php

use dektrium\user\migrations\Migration;

class m151127_125838_trigger_stat extends Migration
{

    public $tbl = '{{%conversion}}';
    public $trs = 'total,scrollEnd,selectText,mouseExit,period';
    public $trFields = 'ignored,wins,queries';

    public function up()
    {
        $type = $this->integer()->notNull()->defaultValue(0);
        $this->addColumn($this->tbl, 'manual_wins', $type);
        $this->addColumn($this->tbl, 'manual_queries', $type);

        $trs = explode(',' , $this->trs);
        $fields = explode(',' , $this->trFields);
        foreach ($trs as $tr) {
            $base = 'tr_' . $tr . '_';
            foreach ($fields as $f) {
                $this->addColumn($this->tbl, $base . $f, $type);
            }
        }
    }

    public function down()
    {
        $trs = array_reverse(explode(',' , $this->trs));
        $fields = array_reverse(explode(',' , $this->trFields));
        foreach ($trs as $tr) {
            $base = 'tr_' . $tr . '_';
            foreach ($fields as $f) {
                $this->dropColumn($this->tbl, $base . $f);
            }
        }
        $this->dropColumn($this->tbl, 'manual_wins');
        $this->dropColumn($this->tbl, 'manual_queries');
    }

}
