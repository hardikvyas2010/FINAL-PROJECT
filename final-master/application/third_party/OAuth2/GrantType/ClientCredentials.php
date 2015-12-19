<?php

namespace OAuth2\GrantType;

use OAuth2\ClientAssertionType\HttpBasic;
use OAuth2\ResponseType\AccessTokenInterface;
use OAuth2\Storage\ClientCredentialsInterface;


class ClientCredentials extends HttpBasic implements GrantTypeInterface
{
    private $clientData;

    public function __construct(ClientCredentialsInterface $storage, array $config = array())
    {

        $config['allow_public_clients'] = false;

        parent::__construct($storage, $config);
    }

    public function getQuerystringIdentifier()
    {
        return 'client_credentials';
    }

    public function getScope()
    {
        $this->loadClientData();

        return isset($this->clientData['scope']) ? $this->clientData['scope'] : null;
    }

    public function getUserId()
    {
        $this->loadClientData();

        return isset($this->clientData['user_id']) ? $this->clientData['user_id'] : null;
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
      
        $includeRefreshToken = false;

        return $accessToken->createAccessToken($client_id, $user_id, $scope, $includeRefreshToken);
    }

    private function loadClientData()
    {
        if (!$this->clientData) {
            $this->clientData = $this->storage->getClientDetails($this->getClientId());
        }
    }
}
