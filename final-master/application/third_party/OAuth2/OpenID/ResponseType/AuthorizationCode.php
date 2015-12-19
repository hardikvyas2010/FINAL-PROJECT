<?php

namespace OAuth2\OpenID\ResponseType;

use OAuth2\ResponseType\AuthorizationCode as BaseAuthorizationCode;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface as AuthorizationCodeStorageInterface;


class AuthorizationCode extends BaseAuthorizationCode implements AuthorizationCodeInterface
{
    public function __construct(AuthorizationCodeStorageInterface $storage, array $config = array())
    {
        parent::__construct($storage, $config);
    }

    public function getAuthorizeResponse($params, $user_id = null)
    {
  
        $result = array('query' => array());

        $params += array('scope' => null, 'state' => null, 'id_token' => null);

        $result['query']['code'] = $this->createAuthorizationCode($params['client_id'], $user_id, $params['redirect_uri'], $params['scope'], $params['id_token']);

        if (isset($params['state'])) {
            $result['query']['state'] = $params['state'];
        }

        return array($params['redirect_uri'], $result);
    }

    public function createAuthorizationCode($client_id, $user_id, $redirect_uri, $scope = null, $id_token = null)
    {
        $code = $this->generateAuthorizationCode();
        $this->storage->setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, time() + $this->config['auth_code_lifetime'], $scope, $id_token);

        return $code;
    }
}
