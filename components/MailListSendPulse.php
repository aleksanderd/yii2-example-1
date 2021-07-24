<?php

namespace app\components;

use Yii;
use yii\base\Component;
use app\components\sendpulse\SendpulseApi;
use yii\base\InvalidConfigException;

/**
 * @property-read SendpulseApi $api
 * @property-read string[] $listsIds
 * @property string[] $listsNames
 */
class MailListSendPulse extends Component implements MailListInterface {

    public $userId;
    public $secret;
    public $prefix = '';

    /** @var SendpulseApi  */
    private $_api;

    /**
     * @return SendpulseApi
     */
    public function getApi()
    {
        return $this->_api;
    }

    public function init()
    {
        if (!(isset($this->userId) && isset($this->secret))) {
            throw new InvalidConfigException('userId and secret required');
        }
        $this->_api = new SendpulseApi($this->userId, $this->secret);
    }

    public function getListsIds()
    {
        return [
            static::LIST_ALL,
            static::LIST_NEW,
            static::LIST_ACTIVE,
            static::LIST_INACTIVE,
            static::LIST_TRASH,
        ];
    }

    private function name2id($name)
    {
        $fullName = $this->prefix . $name;
        $lists = $this->_api->listAddressBooks();
        foreach ($lists as $l) {
            if ($fullName == $l->name) {
                return $l->id;
            }
        }
        $res = $this->_api->createAddressBook($fullName);
        if (isset($res->is_error) && $res->is_error) {
            return null;
        } else {
            return $res->id;
        }
    }

    public function addEmails($list, $emails)
    {
        if (!is_array($emails)) {
            $emails = [$emails];
        }
        $this->_api->addEmails($this->name2id($list), $emails);
    }

    public function removeEmails($list, $emails)
    {
        if (!is_array($emails)) {
            $emails = [$emails];
        }
        $this->_api->removeEmails($this->name2id($list), $emails);
    }

    public function replaceEmails($list, $emails)
    {
        $listId = $this->name2id($list);
        $remoteEmails = $this->_api->getEmailsFromBook($listId);
        if (!is_array($remoteEmails)) {
            $remoteEmails = [];
        } else {
            $t = [];
            foreach ($remoteEmails as $e) {
                $t[] = $e->email;
            }
            $remoteEmails = $t;
        }
        $toAdd = array_diff($emails, $remoteEmails);
        $toDel = array_diff($remoteEmails, $emails);
        if (count($toDel)) {
            $this->_api->removeEmails($listId, $toDel);
        }
        if (count($toAdd)) {
            $this->_api->addEmails($listId, $toAdd);
        }
        return [
            '+' => $toAdd,
            '-' => $toDel,
        ];
    }

}
