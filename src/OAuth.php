<?php

namespace AMO;

use Exception;
use League\OAuth2\Client\Grant\AuthorizationCode;
use League\OAuth2\Client\Token\AccessToken;

class OAuth
{

    public function __construct()
    {
        session_start();
        /**
         * Возвращаем провайдер
         */
        $provider = (new Provider())->returnProvider();

        if (isset($_GET['referer'])) {
            $provider->setBaseDomain($_GET['referer']);
        }

        if (!isset($_GET['code'])) {

            $authorizationUrl = $provider->getAuthorizationUrl(['state' => $_SESSION['oauth2state']]);
            header('Location: ' . $authorizationUrl);
        } elseif (empty($_GET['state']) || empty($_SESSION['oauth2state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        }

        /**
         * Ловим обратный код
         */
        try {
            /** @var AccessToken $access_token */
            $accessToken = $provider->getAccessToken(new AuthorizationCode(), [
                'code' => $_GET['code'],
            ]);

            if (!$accessToken->hasExpired()) {
                (new WorkWithToken())->saveAccessToken([
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $provider->getBaseDomain(),
                ]);
            }
        } catch (Exception $e) {
            die((string)$e);
        }

        header('Location: /');
    }
}
