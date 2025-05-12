<?php

namespace api\components;

use Yii;
use yii\authclient\OAuth2;
use yii\authclient\OAuthToken;

class VKID extends OAuth2
{
    public $authUrl = 'https://id.vk.com/authorize';
    public $tokenUrl = 'https://id.vk.com/oauth2/auth';
    public $apiBaseUrl = 'https://id.vk.com/oauth2/';
    public $enablePkce = true;

    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes(): array|string|null
    {
        return $this->api('user_info', 'POST', [
            'client_id' => $this->clientId,
            'access_token' => $this->getAccessToken()->getToken(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAccessToken($authCode, array $params = []): OAuthToken
    {
        $request = Yii::$app->getRequest();
        $params['device_id'] = $request->get('device_id');
        $token = parent::fetchAccessToken($authCode, $params);
        $token->setParam('device_id', $request->get('device_id'));
        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserAttributes()
    {
        $attributes = parent::getUserAttributes();
        if (empty($attributes['user'])) {
            return [];
        } else {
            return $attributes['user'];
        }
    }
}
