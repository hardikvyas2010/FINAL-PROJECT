<?php

namespace OAuth2\OpenID\GrantType;

use OAuth2\GrantType\AuthorizationCode as BaseAuthorizationCode;
use OAuth2\ResponseType\AccessTokenInterface;

class AuthorizationCode extends BaseAuthorizationCode
{
    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        $includeRefreshToken = true;
        if (isset($this->authCode['id_token'])) {
          
            $scopes = explode(' ', trim($scope));
            $includeRefreshToken = in_array('offline_access', $scopes);
        }

        $token = $accessToken->createAccessToken($client_id, $user_id, $scope, $includeRefreshToken);
        if (isset($this->authCode['id_token'])) {
            $token['id_token'] = $this->authCode['id_token'];
        }

        $this->storage->expireAuthorizationCode($this->authCode['code']);

        return $token;
    }
}
