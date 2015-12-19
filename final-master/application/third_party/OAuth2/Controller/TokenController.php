<?php

namespace OAuth2\Controller;

use OAuth2\ResponseType\AccessTokenInterface;
use OAuth2\ClientAssertionType\ClientAssertionTypeInterface;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\ScopeInterface;
use OAuth2\Scope;
use OAuth2\Storage\ClientInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;

/**
 * @see OAuth2\Controller\TokenControllerInterface
 */
class TokenController implements TokenControllerInterface
{
    protected $accessToken;
    protected $grantTypes;
    protected $clientAssertionType;
    protected $scopeUtil;
    protected $clientStorage;

    public function __construct(AccessTokenInterface $accessToken, ClientInterface $clientStorage, array $grantTypes = array(), ClientAssertionTypeInterface $clientAssertionType = null, ScopeInterface $scopeUtil = null)
    {
        if (is_null($clientAssertionType)) {
            foreach ($grantTypes as $grantType) {
                if (!$grantType instanceof ClientAssertionTypeInterface) {
                    throw new \InvalidArgumentException('You must supply an instance of OAuth2\ClientAssertionType\ClientAssertionTypeInterface or only use grant types which implement OAuth2\ClientAssertionType\ClientAssertionTypeInterface');
                }
            }
        }
        $this->clientAssertionType = $clientAssertionType;
        $this->accessToken = $accessToken;
        $this->clientStorage = $clientStorage;
        foreach ($grantTypes as $grantType) {
            $this->addGrantType($grantType);
        }

        if (is_null($scopeUtil)) {
            $scopeUtil = new Scope();
        }
        $this->scopeUtil = $scopeUtil;
    }

    public function handleTokenRequest(RequestInterface $request, ResponseInterface $response)
    {
        if ($token = $this->grantAccessToken($request, $response)) {
            
            $response->setStatusCode(200);
            $response->addParameters($token);
            $response->addHttpHeaders(array('Cache-Control' => 'no-store', 'Pragma' => 'no-cache'));
        }
    }

    
    public function grantAccessToken(RequestInterface $request, ResponseInterface $response)
    {
        if (strtolower($request->server('REQUEST_METHOD')) != 'post') {
            $response->setError(405, 'invalid_request', 'The request method must be POST when requesting an access token', '#section-3.2');
            $response->addHttpHeaders(array('Allow' => 'POST'));

            return null;
        }

        
        if (!$grantTypeIdentifier = $request->request('grant_type')) {
            $response->setError(400, 'invalid_request', 'The grant type was not specified in the request');

            return null;
        }

        if (!isset($this->grantTypes[$grantTypeIdentifier])) {
          
            $response->setError(400, 'unsupported_grant_type', sprintf('Grant type "%s" not supported', $grantTypeIdentifier));

            return null;
        }

        $grantType = $this->grantTypes[$grantTypeIdentifier];

        if (!$grantType instanceof ClientAssertionTypeInterface) {
            if (!$this->clientAssertionType->validateRequest($request, $response)) {
                return null;
            }
            $clientId = $this->clientAssertionType->getClientId();
        }

    
        if (!$grantType->validateRequest($request, $response)) {
            return null;
        }

        if ($grantType instanceof ClientAssertionTypeInterface) {
            $clientId = $grantType->getClientId();
        } else {
            // validate the Client ID (if applicable)
            if (!is_null($storedClientId = $grantType->getClientId()) && $storedClientId != $clientId) {
                $response->setError(400, 'invalid_grant', sprintf('%s doesn\'t exist or is invalid for the client', $grantTypeIdentifier));

                return null;
            }
        }

        /**
         * Validate the client can use the requested grant type
         */
        if (!$this->clientStorage->checkRestrictedGrantType($clientId, $grantTypeIdentifier)) {
            $response->setError(400, 'unauthorized_client', 'The grant type is unauthorized for this client_id');

            return false;
        }

       
        $requestedScope = $this->scopeUtil->getScopeFromRequest($request);
        $availableScope = $grantType->getScope();

        if ($requestedScope) {
            // validate the requested scope
            if ($availableScope) {
                if (!$this->scopeUtil->checkScope($requestedScope, $availableScope)) {
                    $response->setError(400, 'invalid_scope', 'The scope requested is invalid for this request');

                    return null;
                }
            } else {
                // validate the client has access to this scope
                if ($clientScope = $this->clientStorage->getClientScope($clientId)) {
                    if (!$this->scopeUtil->checkScope($requestedScope, $clientScope)) {
                        $response->setError(400, 'invalid_scope', 'The scope requested is invalid for this client');

                        return false;
                    }
                } elseif (!$this->scopeUtil->scopeExists($requestedScope)) {
                    $response->setError(400, 'invalid_scope', 'An unsupported scope was requested');

                    return null;
                }
            }
        } elseif ($availableScope) {
            // use the scope associated with this grant type
            $requestedScope = $availableScope;
        } else {
            // use a globally-defined default scope
            $defaultScope = $this->scopeUtil->getDefaultScope($clientId);

            // "false" means default scopes are not allowed
            if (false === $defaultScope) {
                $response->setError(400, 'invalid_scope', 'This application requires you specify a scope parameter');

                return null;
            }

            $requestedScope = $defaultScope;
        }

        return $grantType->createAccessToken($this->accessToken, $clientId, $grantType->getUserId(), $requestedScope);
    }


    public function addGrantType(GrantTypeInterface $grantType, $identifier = null)
    {
        if (is_null($identifier) || is_numeric($identifier)) {
            $identifier = $grantType->getQuerystringIdentifier();
        }

        $this->grantTypes[$identifier] = $grantType;
    }
}
