<?php

namespace OAuth2\Controller;

use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;


interface AuthorizeControllerInterface
{
  
    const RESPONSE_TYPE_AUTHORIZATION_CODE = 'code';
    const RESPONSE_TYPE_ACCESS_TOKEN = 'token';

    public function handleAuthorizeRequest(RequestInterface $request, ResponseInterface $response, $is_authorized, $user_id = null);

    public function validateAuthorizeRequest(RequestInterface $request, ResponseInterface $response);
}
