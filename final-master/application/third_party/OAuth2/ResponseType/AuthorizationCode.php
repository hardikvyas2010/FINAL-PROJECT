<?php

namespace OAuth2\ResponseType;

use OAuth2\Storage\AuthorizationCodeInterface as AuthorizationCodeStorageInterface;


class AuthorizationCode implements AuthorizationCodeInterface
{
    protected $storage;
    protected $config;

    public function __construct(AuthorizationCodeStorageInterface $storage, array $config = array())
    {
        $this->storage = $storage;
        $this->config = array_merge(array(
            'enforce_redirect' => false,
            'auth_code_lifetime' => 30,
        ), $config);
    }

    public function getAuthorizeResponse($params, $user_id = null)
    {
        // build the URL to redirect to
        $result = array('query' => array());

        $params += array('scope' => null, 'state' => null);

        $result['query']['code'] = $this->createAuthorizationCode($params['client_id'], $user_id, $params['redirect_uri'], $params['scope']);

        if (isset($params['state'])) {
            $result['query']['state'] = $params['state'];
        }

        return array($params['redirect_uri'], $result);
    }


    public function createAuthorizationCode($client_id, $user_id, $redirect_uri, $scope = null)
    {
        $code = $this->generateAuthorizationCode();
        $this->storage->setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, time() + $this->config['auth_code_lifetime'], $scope);

        return $code;
    }

    public function enforceRedirect()
    {
        return $this->config['enforce_redirect'];
    }

  
    protected function generateAuthorizationCode()
    {
        $tokenLen = 40;
        if (function_exists('mcrypt_create_iv')) {
            $randomData = mcrypt_create_iv(100, MCRYPT_DEV_URANDOM);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $randomData = openssl_random_pseudo_bytes(100);
        } elseif (@file_exists('/dev/urandom')) { // Get 100 bytes of random data
            $randomData = file_get_contents('/dev/urandom', false, null, 0, 100) . uniqid(mt_rand(), true);
        } else {
            $randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid(mt_rand(), true);
        }

        return substr(hash('sha512', $randomData), 0, $tokenLen);
    }
}
