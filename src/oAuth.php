<?php

namespace AMO;

use AmoCRM\OAuth2\Client\Provider\AmoCRMResourceOwner;
use Exception;
use League\OAuth2\Client\Grant\AuthorizationCode;
use League\OAuth2\Client\Token\AccessToken;

class oAuth
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
            /**
             * Просто отображаем кнопку авторизации или получаем ссылку для авторизации
             * По-умолчанию - отображаем кнопку
             */
            $_SESSION['oauth2state'] = bin2hex(random_bytes(16));
            if (true) {
                echo '<div>
                <script
                    class="amocrm_oauth"
                    charset="utf-8"
                    data-client-id="' . $provider->getClientId() . '"
                    data-title="Установить интеграцию"
                    data-compact="false"
                    data-class-name="className"
                    data-color="default"
                    data-state="' . $_SESSION['oauth2state'] . '"
                    data-error-callback="handleOauthError"
                    src="https://www.amocrm.ru/auth/button.min.js"
                ></script>
                </div>';
                echo '<script>
            handleOauthError = function(event) {
                alert(\'ID клиента - \' + event.client_id + \' Ошибка - \' + event.error);
            }
            </script>';
                die;
            } else {
                $authorizationUrl = $provider->getAuthorizationUrl(['state' => $_SESSION['oauth2state']]);
                header('Location: ' . $authorizationUrl);
            }
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

        /** @var AmoCRMResourceOwner $ownerDetails */
        $ownerDetails = $provider->getResourceOwner($accessToken);

        header('Location: /');
    }
}
