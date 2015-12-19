<?php

namespace OAuth2\OpenID\Controller;

use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;


interface UserInfoControllerInterface
{
    public function handleUserInfoRequest(RequestInterface $request, ResponseInterface $response);
}
