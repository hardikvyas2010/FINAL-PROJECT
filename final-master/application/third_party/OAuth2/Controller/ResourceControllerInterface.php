<?php

namespace OAuth2\Controller;

use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;


interface ResourceControllerInterface
{
    public function verifyResourceRequest(RequestInterface $request, ResponseInterface $response, $scope = null);

    public function getAccessTokenData(RequestInterface $request, ResponseInterface $response);
}
