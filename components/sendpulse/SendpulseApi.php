<?php

namespace app\components\sendpulse;

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'sendpulseInterface.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'sendpulse.php');

use Yii;
use yii\helpers\FileHelper;

class SendpulseApi extends \SendpulseApi {

    private $apiFilesPath = '';

    public function __construct($userId, $secret, $storageType = 'file')
    {
        $this->apiFilesPath = Yii::getAlias('@runtime/cache/sendpulse/');
        FileHelper::createDirectory($this->apiFilesPath);
        parent::__construct($userId, $secret, $storageType);
    }

    public function getEmailsFromBook($id) {
        if( empty( $id ) ) {
            return $this->handleError( 'Empty book id' );
        }
        $result = [];
        $offset = 0;
        while (true) {
            $requestResult = $this->sendRequest( 'addressbooks/' . $id . '/emails' , 'GET', [
                'limit' => 100,
                'offset' => $offset,
            ]);
            $res = $this->handleResult($requestResult);
            if (isset($res->is_error)) {
                print_r($res);
                continue;
            }
            if (is_array($res)) {
                $result = array_merge($result, $res);
            }
            if (count($requestResult->data) < 100) {
                break;
            }
            $offset += 100;
        }
        return $result;
    }
}
