<?php

use app\models\ReferralUrl;
use dektrium\user\migrations\Migration;

class m151020_205931_refurl_code_gift extends Migration
{
    public function up()
    {
        $this->addColumn('{{%referral_url}}', 'code', $this->string(11)->unique());
        $this->addColumn('{{%referral_url}}', 'gift_amount', $this->decimal(19, 2)->notNull()->defaultValue(0));
        $this->addColumn('{{%referral_stats}}', 'gifts_activated', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('{{%referral_stats}}', 'gifts_paid', $this->decimal(19, 2)->notNull()->defaultValue(0));

        /** @var ReferralUrl[] $urls */
        $urls = ReferralUrl::find()->all();
        foreach ($urls as $u) {
            if (!isset($u->code)) {
                $u->code = $u->generateCode();
                $u->save(false, ['code']);
            }
        }

    }

    public function down()
    {
        $this->dropColumn('{{%referral_stats}}', 'gifts_paid');
        $this->dropColumn('{{%referral_stats}}', 'gifts_activated');
        $this->dropColumn('{{%referral_url}}', 'gift_amount');
        $this->dropColumn('{{%referral_url}}', 'code');
    }

}

