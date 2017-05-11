<?php

namespace mobilejazz\yii2\oauth2server\grants;

use mobilejazz\yii2\oauth2server\models\SocialAuthForm;
use OAuth2\ClientAssertionType\HttpBasic;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use OAuth2\Storage\ClientCredentialsInterface;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;

class SocialCredentials extends HttpBasic implements GrantTypeInterface
{

    public $auth_url;

    private $userInfo;

    public function __construct(ClientCredentialsInterface $storage, array $config = array())
    {
        parent::__construct($storage, $config);

        $this->auth_url = $config['auth_url'];

        if(!isset($this->auth_url)) throw new InvalidConfigException('auth_url must be set');
    }


    public function getQuerystringIdentifier()
    {
        return 'social_credentials';
    }

    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {

        // validate client first

        if(!parent::validateRequest($request, $response)) return false;

        // validate social credentials against remote api

        $model = new SocialAuthForm([
            'provider' => $request->request('provider'),
            'client_id' => $request->request('client_id'),
            'client_secret' => $request->request('client_secret'),
            'facebook_token' => $request->request('facebook_token'),
            'google_id_token' => $request->request('google_id_token')
        ]);

        if(!$model->validate()) {
            $response->setError(422, 'invalid_request', $model->getFirstErrors()[0]);
            return null;
        }

        if (!$this->checkSocialCredentials($model)) {
            $response->setError(401, 'invalid_grant', 'Invalid provider and external_user_id combination');
            return null;
        }

        if (empty($this->userInfo)) {
            $response->setError(400, 'invalid_grant', 'Unable to retrieve user information');

            return null;
        }

        return true;
    }

    /**
     * @param $model SocialAuthForm
     * @return bool
     * @throws Exception
     */
    private function checkSocialCredentials($model)
    {
        /** @var Client $client */
        $client = new Client();

        // request the remove user api to validate the username and password for us

        $data = [ 'provider' => $model->provider ];

        switch($model->provider){
            case 'facebook':
                $data['facebook_token'] = $model->facebook_token;
                break;
            case 'google':
                $data['google_id_token'] = $model->google_id_token;
                break;
            default:
                throw new Exception('Unhandled provider: ' . $model->provider);
        }

        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl($this->auth_url)
            ->setData($data)
            ->send();

        if($response->isOk) $this->userInfo = $response->data;

        return $response->isOk;
    }

    public function getUserId()
    {
        return $this->userInfo['id'];
    }

    public function getScope()
    {
        return '';  // empty for now
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        return $accessToken->createAccessToken($client_id, $user_id, $scope, true);
    }

}