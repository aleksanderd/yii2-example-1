<?php

namespace jet\storage;

use GuzzleHttp\Client;
use yii\base\Component;

/**
 *
 */
class StorageS3 extends Component implements StorageInterface
{
    public $profile = 'default';
    /**
     * @var string Amazon access key
     */
    public $key;
    /**
     * @var string Amazon secret access key
     */
    public $secret;
    /**
     * @var string specifies the AWS region
     */
    public $region;
    /**
     * @var string specifies the AWS version
     */
    public $version = '2006-03-01';
    /**
     * @var string Amazon Bucket
     */
    public $bucket;

    public $acl = 'public-read';
    /**
     * @var \Aws\S3\S3Client
     */
    private $_client;
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->_client = \Aws\S3\S3Client::factory([
            'credentials' => array(
                'key'    => $this->key,
                'secret' => $this->secret,
            ),
            'region'      => $this->region,
            'version'     => $this->version,
        ]);
    }

    /**
     * Saves a file
     * @param string $filePath Полный путь к файлу для закачки, с указанием протокола, сервера и пути.
     * @param string $fileName имя файла для записи, может содержать путь относительно bucket.
     * @param string|bool $bucket в какое хранилище помещать файл, если не указан - в хранилище по умолчанию.
     * @return \Guzzle\Service\Resource\Model
     */
    public function save($filePath, $fileName, $bucket = false)
    {
        if (!$bucket) {
            $bucket = $this->bucket;
        }
        try {
            echo "<pre>";
            $client   = new Client();
            $response = $client->get($filePath);
            $body     = $response->getBody();
            $result   = $this->_client->upload($bucket, $fileName, $body, $this->acl);
            return $result->get('ObjectURL');
        } catch (\Exception $e) {
            return false;
        }
    }
}
