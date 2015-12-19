<?php

namespace OAuth2\ResponseType;


interface AuthorizationCodeInterface extends ResponseTypeInterface
{
  
    public function enforceRedirect();

   
    public function createAuthorizationCode($client_id, $user_id, $redirect_uri, $scope = null);
}
