<?php

namespace mobilejazz\yii2\oauth2server\storage;

use OAuth2\Storage\UserCredentialsInterface;
use yii\httpclient\Client;

/**
 * Sources validation of credentials from a remote source
 *
 * @package mobilejazz\yii2\oauth2server\storage
 */
class RemoteUserCredentials implements UserCredentialsInterface
{

    public $auth_url;

    private $user;

    /**
     * @inheritdoc
     */
    public function checkUserCredentials($username, $password)
    {
        /** @var Client $client */
        $client = new Client();

        // request the remove user api to validate the username and password for us

        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl($this->auth_url)
            ->setData([
                'email' => $username,
                'password' => $password
            ])
            ->send();

        if($response->isOk) $this->user = $response->data;

        return $response->isOk;
    }

    /**
     * @inheritdoc
     */
    public function getUserDetails($username)
    {

        return [
            'user_id' => $this->user['id'],
            'scopes' => ''
        ];

    }
}