<?php

namespace OAuth2\ResponseType;

use OAuth2\Storage\AccessTokenInterface as AccessTokenStorageInterface;
use OAuth2\Storage\RefreshTokenInterface;

class AccessToken implements AccessTokenInterface
{
    protected $tokenStorage;
    protected $refreshStorage;
    protected $config;


    public function __construct(AccessTokenStorageInterface $tokenStorage, RefreshTokenInterface $refreshStorage = null, array $config = array())
    {
        $this->tokenStorage = $tokenStorage;
        $this->refreshStorage = $refreshStorage;

        $this->config = array_merge(array(
            'token_type'             => 'bearer',
            'access_lifetime'        => 3600,
            'refresh_token_lifetime' => 1209600,
        ), $config);
    }

    public function getAuthorizeResponse($params, $user_id = null)
    {
        // build the URL to redirect to
        $result = array('query' => array());

        $params += array('scope' => null, 'state' => null);

     
        $includeRefreshToken = false;
        $result["fragment"] = $this->createAccessToken($params['client_id'], $user_id, $params['scope'], $includeRefreshToken);

        if (isset($params['state'])) {
            $result["fragment"]["state"] = $params['state'];
        }

        return array($params['redirect_uri'], $result);
    }

 
    public function createAccessToken($client_id, $user_id, $scope = null, $includeRefreshToken = true)
    {
        $token = array(
            "access_token" => $this->generateAccessToken(),
            "expires_in" => $this->config['access_lifetime'],
            "token_type" => $this->config['token_type'],
            "scope" => $scope
        );

        $this->tokenStorage->setAccessToken($token["access_token"], $client_id, $user_id, $this->config['access_lifetime'] ? time() + $this->config['access_lifetime'] : null, $scope);

        if ($includeRefreshToken && $this->refreshStorage) {
            $token["refresh_token"] = $this->generateRefreshToken();
            $expires = 0;
            if ($this->config['refresh_token_lifetime'] > 0) {
                $expires = time() + $this->config['refresh_token_lifetime'];
            }
            $this->refreshStorage->setRefreshToken($token['refresh_token'], $client_id, $user_id, $expires, $scope);
        }

        return $token;
    }

 
    protected function generateAccessToken()
    {
        if (function_exists('mcrypt_create_iv')) {
            $randomData = mcrypt_create_iv(20, MCRYPT_DEV_URANDOM);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $randomData = openssl_random_pseudo_bytes(20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        } 
        if (@file_exists('/dev/urandom')) { // Get 100 bytes of random data
            $randomData = file_get_contents('/dev/urandom', false, null, 0, 20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        // Last resort which you probably should just get rid of:
        $randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid(mt_rand(), true);
        return substr(hash('sha512', $randomData), 0, 40);
    }


    protected function generateRefreshToken()
    {
        return $this->generateAccessToken(); // let's reuse the same scheme for token generation
    }
}
