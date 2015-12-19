<?php

namespace OAuth2\Controller;

use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;


interface TokenControllerInterface
{

    public function handleTokenRequest(RequestInterface $request, ResponseInterface $response);

    public function grantAccessToken(RequestInterface $request, ResponseInterface $response);
}
