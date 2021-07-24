<?php

namespace flyiing\translation\commands;

class MessageController extends \yii\console\controllers\MessageController {

    protected function saveMessagesToDb($messages, $db, $sourceMessageTable, $messageTable, $removeUnused, $languages, $markUnused)
    {
        /**
         * Добавляем текущие значения, чтобы не помечать unused как @@...@@
         */
        $q = new \yii\db\Query;
        foreach ($q->select(['id', 'category', 'message'])->from($sourceMessageTable)->all() as $row) {
            if (!(isset($messages[$row['category']]) && in_array($row['message'], $messages[$row['category']]))) {
                $messages[$row['category']][] = $row['message'];
            }
        }
        parent::saveMessagesToDb($messages, $db, $sourceMessageTable, $messageTable, $removeUnused, $languages, $markUnused);
    }

}