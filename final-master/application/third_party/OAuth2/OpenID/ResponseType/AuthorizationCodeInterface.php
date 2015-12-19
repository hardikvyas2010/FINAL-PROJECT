<?php

namespace OAuth2\OpenID\ResponseType;

use OAuth2\ResponseType\AuthorizationCodeInterface as BaseAuthorizationCodeInterface;

interface AuthorizationCodeInterface extends BaseAuthorizationCodeInterface
{
  
    public function createAuthorizationCode($client_id, $user_id, $redirect_uri, $scope = null, $id_token = null);
}
