<?php

namespace AMO;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use League\OAuth2\Client\Token\AccessTokenInterface;

class Provider
{
    private string $clientId = '11594fab-72aa-4d79-8a27-c5ea2a4c15ff';
    private string $clientSecret = 'fqyLCDpIpxIvxoVUJiGgPecsQkMTBQuGBWlr3kJo2c6maikQACRmkTgMIYH8AgMD';
    private string $redirectUri = 'http://localhost:8000/getaccess.php';

    public function returnProvider(): AmoCRM
    {
        return new AmoCRM([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $this->redirectUri,
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
