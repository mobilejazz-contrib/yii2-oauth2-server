<?php

namespace mobilejazz\yii2\oauth2server\models;

use api\modules\v1\components\Facebook;
use api\modules\v1\components\Google;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\web\BadRequestHttpException;

class SocialAuthForm extends Model
{

    const PROVIDERS = ['google', 'facebook', 'linkedin'];

    public $provider;
    public $facebook_token;
    public $google_id_token;
    public $client_id;
    public $client_secret;

    public function rules()
    {
        return [
            [['provider', 'client_id', 'client_secret'], 'required'],
            ['facebook_token', 'required', 'when' => function($model){
                return $model->provider === 'facebook';
            }],
            ['google_id_token', 'required', 'when' => function($model){
                return $model->provider === 'google';
            }],
            [['provider', 'facebook_token', 'google_id_token'], 'safe'],
            [['provider', 'facebook_token', 'google_id_token'], 'trim'],
            [['provider', 'client_id', 'client_secret'], 'string', 'max' => 32],
            [['facebook_token', 'google_id_token'], 'string', 'max' => 256],
            ['provider', 'in', 'range' => self::PROVIDERS, 'message' => 'Valid provider: ' . implode(', ', self::PROVIDERS)],
        ];
    }

}