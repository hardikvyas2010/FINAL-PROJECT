<?php

namespace OAuth2\ClientAssertionType;

use OAuth2\Storage\ClientCredentialsInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;


class HttpBasic implements ClientAssertionTypeInterface
{
    private $clientData;

    protected $storage;
    protected $config;

   
    public function __construct(ClientCredentialsInterface $storage, array $config = array())
    {
        $this->storage = $storage;
        $this->config = array_merge(array(
            'allow_credentials_in_request_body' => true,
            'allow_public_clients' => true,
        ), $config);
    }

    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {
        if (!$clientData = $this->getClientCredentials($request, $response)) {
            return false;
        }

        if (!isset($clientData['client_id'])) {
            throw new \LogicException('the clientData array must have "client_id" set');
        }

        if (!isset($clientData['client_secret']) || $clientData['client_secret'] == '') {
            if (!$this->config['allow_public_clients']) {
                $response->setError(400, 'invalid_client', 'client credentials are required');

                return false;
            }

            if (!$this->storage->isPublicClient($clientData['client_id'])) {
                $response->setError(400, 'invalid_client', 'This client is invalid or must authenticate using a client secret');

                return false;
            }
        } elseif ($this->storage->checkClientCredentials($clientData['client_id'], $clientData['client_secret']) === false) {
            $response->setError(400, 'invalid_client', 'The client credentials are invalid');

            return false;
        }

        $this->clientData = $clientData;

        return true;
    }

    public function getClientId()
    {
        return $this->clientData['client_id'];
    }

    
    public function getClientCredentials(RequestInterface $request, ResponseInterface $response = null)
    {
        if (!is_null($request->headers('PHP_AUTH_USER')) && !is_null($request->headers('PHP_AUTH_PW'))) {
            return array('client_id' => $request->headers('PHP_AUTH_USER'), 'client_secret' => $request->headers('PHP_AUTH_PW'));
        }

        if ($this->config['allow_credentials_in_request_body']) {
            // Using POST for HttpBasic authorization is not recommended, but is supported by specification
            if (!is_null($request->request('client_id'))) {
                
                return array('client_id' => $request->request('client_id'), 'client_secret' => $request->request('client_secret'));
            }
        }

        if ($response) {
            $message = $this->config['allow_credentials_in_request_body'] ? ' or body' : '';
            $response->setError(400, 'invalid_client', 'Client credentials were not found in the headers'.$message);
        }

        return null;
    }
}
