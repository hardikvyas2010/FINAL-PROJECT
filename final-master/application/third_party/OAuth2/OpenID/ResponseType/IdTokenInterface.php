<?php

namespace OAuth2\OpenID\ResponseType;

use OAuth2\ResponseType\ResponseTypeInterface;

interface IdTokenInterface extends ResponseTypeInterface
{

    public function createIdToken($client_id, $userInfo, $nonce = null, $userClaims = null, $access_token = null);
}
