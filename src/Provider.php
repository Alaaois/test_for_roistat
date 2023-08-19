<?php

namespace AMO;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\Dotenv\Dotenv;

class Provider
{
    private string $clientId;

    private string $clientSecret;
    private string $redirectUri;

    public function __construct()
    {
        $this->loadEnv();
    }

    private function loadEnv(): void
    {
        (new Dotenv())->load('.env');
        $this->clientId = $_ENV['CLIENT_ID'];
        $this->clientSecret = $_ENV['CLIENT_SECRET'];
        $this->redirectUri = $_ENV['REDIRECT_URI'];
    }

    public function returnProvider(): AmoCRM
    {
        return new AmoCRM([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $this->redirectUri
        ]);
    }

    public function returnApiClient(): AmoCRMApiClient
    {
        $apiClient = new AmoCRMApiClient($this->clientId, $this->clientSecret, $this->redirectUri);
        $accessToken = (new WorkWithToken())->getAccessToken();
        $apiClient->setAccessToken($accessToken)
            ->setAccountBaseDomain($accessToken->getValues()['baseDomain'])
            ->onAccessTokenRefresh(
                function (AccessTokenInterface $accessToken, string $baseDomain) {
                    saveToken(
                        [
                            'accessToken' => $accessToken->getToken(),
                            'refreshToken' => $accessToken->getRefreshToken(),
                            'expires' => $accessToken->getExpires(),
                            'baseDomain' => $baseDomain,
                        ]
                    );
                }
            );
        return $apiClient;
    }
}
