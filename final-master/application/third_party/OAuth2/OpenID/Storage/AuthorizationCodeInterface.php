<?php

namespace OAuth2\OpenID\Storage;

use OAuth2\Storage\AuthorizationCodeInterface as BaseAuthorizationCodeInterface;

interface AuthorizationCodeInterface extends BaseAuthorizationCodeInterface
{

    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null);
}
