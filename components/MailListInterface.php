<?php

namespace app\components;

interface MailListInterface {

    /** Список рассылки для **всех** пользователей. */
    const LIST_ALL = 'all';
    /** Список рассылки для **новых** пользователей. */
    const LIST_NEW = 'new';
    /** Список рассылки для **активных** пользователей. */
    const LIST_ACTIVE = 'active';
    /** Список рассылки для **неактивных** пользователей. */
    const LIST_INACTIVE = 'inactive';
    /** Список рассылки для **удалённых** пользователей. */
    const LIST_TRASH = 'trash';

    /**
     * Добавляет пользователей в список рассылки.
     * @param string $list Имя списка рассылки
     * @param \app\models\User[] $emails Массив адресов
     * @return mixed
     */
    public function addEmails($list, $emails);

    /**
     * Удаляет пользователей из списка рассылки.
     * @param string $list Имя списка рассылки
     * @param string[] $emails Массив адресов
     * @return mixed
     */
    public function removeEmails($list, $emails);

    /**
     * Полностью заменяет список рассылки(удаляет и добавляет).
     * @param string $list Имя списка рассылки
     * @param string[] $emails Массив адресов
     * @return mixed
     */
    public function replaceEmails($list, $emails);

}
