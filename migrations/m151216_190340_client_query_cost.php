<?php

use app\models\ClientQuery;
use yii\db\Migration;

class m151216_190340_client_query_cost extends Migration
{
    public function up()
    {
        $this->addColumn('{{%client_query}}', 'cost', $this->decimal(19, 4)->defaultValue(0) . ' AFTER `client_cost`');

        $queries = ClientQuery::find()->where(['>=', 'status', ClientQuery::STATUS_POOL_CONN])->all();
        foreach ($queries as $query) {
            /** @var \app\models\ClientQuery $query */
            $query->cost = $query->getCalls()->sum('cost');
            $query->save(false, ['cost']);
        }

    }

    public function down()
    {
        $this->dropColumn('{{%client_query}}', 'cost');
    }

}
