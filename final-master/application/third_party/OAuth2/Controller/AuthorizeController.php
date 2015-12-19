<?php

namespace OAuth2\Controller;

use OAuth2\Storage\ClientInterface;
use OAuth2\ScopeInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\Scope;


class AuthorizeController implements AuthorizeControllerInterface
{
    private $scope;
    private $state;
    private $client_id;
    private $redirect_uri;
    private $response_type;

    protected $clientStorage;
    protected $responseTypes;
    protected $config;
    protected $scopeUtil;

  
    public function __construct(ClientInterface $clientStorage, array $responseTypes = array(), array $config = array(), ScopeInterface $scopeUtil = null)
    {
        $this->clientStorage = $clientStorage;
        $this->responseTypes = $responseTypes;
        $this->config = array_merge(array(
            'allow_implicit' => false,
            'enforce_state'  => true,
            'require_exact_redirect_uri' => true,
            'redirect_status_code' => 302,
        ), $config);

        if (is_null($scopeUtil)) {
            $scopeUtil = new Scope();
        }
        $this->scopeUtil = $scopeUtil;
    }

    public function handleAuthorizeRequest(RequestInterface $request, ResponseInterface $response, $is_authorized, $user_id = null)
    {
        if (!is_bool($is_authorized)) {
            throw new \InvalidArgumentException('Argument "is_authorized" must be a boolean.  This method must know if the user has granted access to the client.');
        }

       
        if (!$this->validateAuthorizeRequest($request, $response)) {
            return;
        }

        // If no redirect_uri is passed in the request, use client's registered one
        if (empty($this->redirect_uri)) {
            $clientData              = $this->clientStorage->getClientDetails($this->client_id);
            $registered_redirect_uri = $clientData['redirect_uri'];
        }

        // the user declined access to the client's application
        if ($is_authorized === false) {
            $redirect_uri = $this->redirect_uri ?: $registered_redirect_uri;
            $this->setNotAuthorizedResponse($request, $response, $redirect_uri, $user_id);

            return;
        }

        // build the parameters to set in the redirect URI
        if (!$params = $this->buildAuthorizeParameters($request, $response, $user_id)) {
            return;
        }

        $authResult = $this->responseTypes[$this->response_type]->getAuthorizeResponse($params, $user_id);

        list($redirect_uri, $uri_params) = $authResult;

        if (empty($redirect_uri) && !empty($registered_redirect_uri)) {
            $redirect_uri = $registered_redirect_uri;
        }

        $uri = $this->buildUri($redirect_uri, $uri_params);

        // return redirect response
        $response->setRedirect($this->config['redirect_status_code'], $uri);
    }

    protected function setNotAuthorizedResponse(RequestInterface $request, ResponseInterface $response, $redirect_uri, $user_id = null)
    {
        $error = 'access_denied';
        $error_message = 'The user denied access to your application';
        $response->setRedirect($this->config['redirect_status_code'], $redirect_uri, $this->state, $error, $error_message);
    }

   
    protected function buildAuthorizeParameters($request, $response, $user_id)
    {
        // @TODO: we should be explicit with this in the future
        $params = array(
            'scope'         => $this->scope,
            'state'         => $this->state,
            'client_id'     => $this->client_id,
            'redirect_uri'  => $this->redirect_uri,
            'response_type' => $this->response_type,
        );

        return $params;
    }

    public function validateAuthorizeRequest(RequestInterface $request, ResponseInterface $response)
    {
        // Make sure a valid client id was supplied (we can not redirect because we were unable to verify the URI)
        if (!$client_id = $request->query('client_id', $request->request('client_id'))) {
            // We don't have a good URI to use
            $response->setError(400, 'invalid_client', "No client id supplied");

            return false;
        }

        // Get client details
        if (!$clientData = $this->clientStorage->getClientDetails($client_id)) {
            $response->setError(400, 'invalid_client', 'The client id supplied is invalid');

            return false;
        }

        $registered_redirect_uri = isset($clientData['redirect_uri']) ? $clientData['redirect_uri'] : '';

        if ($supplied_redirect_uri = $request->query('redirect_uri', $request->request('redirect_uri'))) {
            // validate there is no fragment supplied
            $parts = parse_url($supplied_redirect_uri);
            if (isset($parts['fragment']) && $parts['fragment']) {
                $response->setError(400, 'invalid_uri', 'The redirect URI must not contain a fragment');

                return false;
            }

            // validate against the registered redirect uri(s) if available
            if ($registered_redirect_uri && !$this->validateRedirectUri($supplied_redirect_uri, $registered_redirect_uri)) {
                $response->setError(400, 'redirect_uri_mismatch', 'The redirect URI provided is missing or does not match', '#section-3.1.2');

                return false;
            }
            $redirect_uri = $supplied_redirect_uri;
        } else {
            // use the registered redirect_uri if none has been supplied, if possible
            if (!$registered_redirect_uri) {
                $response->setError(400, 'invalid_uri', 'No redirect URI was supplied or stored');

                return false;
            }

            if (count(explode(' ', $registered_redirect_uri)) > 1) {
                $response->setError(400, 'invalid_uri', 'A redirect URI must be supplied when multiple redirect URIs are registered', '#section-3.1.2.3');

                return false;
            }
            $redirect_uri = $registered_redirect_uri;
        }

        // Select the redirect URI
        $response_type = $request->query('response_type', $request->request('response_type'));

        // for multiple-valued response types - make them alphabetical
        if (false !== strpos($response_type, ' ')) {
            $types = explode(' ', $response_type);
            sort($types);
            $response_type = ltrim(implode(' ', $types));
        }

        $state = $request->query('state', $request->request('state'));

        // type and client_id are required
        if (!$response_type || !in_array($response_type, $this->getValidResponseTypes())) {
            $response->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'invalid_request', 'Invalid or missing response type', null);

            return false;
        }

        if ($response_type == self::RESPONSE_TYPE_AUTHORIZATION_CODE) {
            if (!isset($this->responseTypes['code'])) {
                $response->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'unsupported_response_type', 'authorization code grant type not supported', null);

                return false;
            }
            if (!$this->clientStorage->checkRestrictedGrantType($client_id, 'authorization_code')) {
                $response->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'unauthorized_client', 'The grant type is unauthorized for this client_id', null);

                return false;
            }
            if ($this->responseTypes['code']->enforceRedirect() && !$redirect_uri) {
                $response->setError(400, 'redirect_uri_mismatch', 'The redirect URI is mandatory and was not supplied');

                return false;
            }
        } else {
            if (!$this->config['allow_implicit']) {
                $response->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'unsupported_response_type', 'implicit grant type not supported', null);

                return false;
            }
            if (!$this->clientStorage->checkRestrictedGrantType($client_id, 'implicit')) {
                $response->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'unauthorized_client', 'The grant type is unauthorized for this client_id', null);

                return false;
            }
        }

        // validate requested scope if it exists
        $requestedScope = $this->scopeUtil->getScopeFromRequest($request);

        if ($requestedScope) {
          
            $clientScope = $this->clientStorage->getClientScope($client_id);
            if ((is_null($clientScope) && !$this->scopeUtil->scopeExists($requestedScope))
                || ($clientScope && !$this->scopeUtil->checkScope($requestedScope, $clientScope))) {
                $response->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'invalid_scope', 'An unsupported scope was requested', null);

                return false;
            }
        } else {
           
            $defaultScope = $this->scopeUtil->getDefaultScope($client_id);

            if (false === $defaultScope) {
                $response->setRedirect($this->config['redirect_status_code'], $redirect_uri, $state, 'invalid_client', 'This application requires you specify a scope parameter', null);

                return false;
            }

            $requestedScope = $defaultScope;
        }

        // Validate state parameter exists (if configured to enforce this)
        if ($this->config['enforce_state'] && !$state) {
            $response->setRedirect($this->config['redirect_status_code'], $redirect_uri, null, 'invalid_request', 'The state parameter is required');

            return false;
        }

        // save the input data and return true
        $this->scope         = $requestedScope;
        $this->state         = $state;
        $this->client_id     = $client_id;
        // Only save the SUPPLIED redirect URI (@see http://tools.ietf.org/html/rfc6749#section-4.1.3)
        $this->redirect_uri  = $supplied_redirect_uri;
        $this->response_type = $response_type;

        return true;
    }

 
 
    private function buildUri($uri, $params)
    {
        $parse_url = parse_url($uri);

        // Add our params to the parsed uri
        foreach ($params as $k => $v) {
            if (isset($parse_url[$k])) {
                $parse_url[$k] .= "&" . http_build_query($v, '', '&');
            } else {
                $parse_url[$k] = http_build_query($v, '', '&');
            }
        }

        // Put humpty dumpty back together
        return
            ((isset($parse_url["scheme"])) ? $parse_url["scheme"] . "://" : "")
            . ((isset($parse_url["user"])) ? $parse_url["user"]
            . ((isset($parse_url["pass"])) ? ":" . $parse_url["pass"] : "") . "@" : "")
            . ((isset($parse_url["host"])) ? $parse_url["host"] : "")
            . ((isset($parse_url["port"])) ? ":" . $parse_url["port"] : "")
            . ((isset($parse_url["path"])) ? $parse_url["path"] : "")
            . ((isset($parse_url["query"]) && !empty($parse_url['query'])) ? "?" . $parse_url["query"] : "")
            . ((isset($parse_url["fragment"])) ? "#" . $parse_url["fragment"] : "")
        ;
    }

    protected function getValidResponseTypes()
    {
        return array(
            self::RESPONSE_TYPE_ACCESS_TOKEN,
            self::RESPONSE_TYPE_AUTHORIZATION_CODE,
        );
    }

    \
    protected function validateRedirectUri($inputUri, $registeredUriString)
    {
        if (!$inputUri || !$registeredUriString) {
            return false; // if either one is missing, assume INVALID
        }

        $registered_uris = preg_split('/\s+/', $registeredUriString);
        foreach ($registered_uris as $registered_uri) {
            if ($this->config['require_exact_redirect_uri']) {
                // the input uri is validated against the registered uri using exact match
                if (strcmp($inputUri, $registered_uri) === 0) {
                    return true;
                }
            } else {
                
                if (strcasecmp(substr($inputUri, 0, strlen($registered_uri)), $registered_uri) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

 

    public function getScope()
    {
        return $this->scope;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    public function getResponseType()
    {
        return $this->response_type;
    }
}
