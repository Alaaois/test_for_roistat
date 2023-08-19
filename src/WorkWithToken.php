<?php

namespace AMO;

use League\OAuth2\Client\Token\AccessToken;

const TOKEN_FILE = 'token_info.json';

class WorkWithToken
{
    public function saveAccessToken($accessToken): void
    {
        if (
            isset($accessToken['refreshToken']) && isset($accessToken['accessToken']) && isset($accessToken['baseDomain']) && isset($accessToken['expires'])
        ) {
            $data = [
                'accessToken' => $accessToken['accessToken'],
                'expires' => $accessToken['expires'],
                'refreshToken' => $accessToken['refreshToken'],
                'baseDomain' => $accessToken['baseDomain'],
            ];

            file_put_contents(TOKEN_FILE, json_encode($data));
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }

    /**
     * @return AccessToken
     */
    public function getAccessToken(): AccessToken
    {
        $accessToken = json_decode(file_get_contents(TOKEN_FILE), true);
        if (
            isset($accessToken['refreshToken']) && isset($accessToken['accessToken']) && isset($accessToken['baseDomain']) && isset($accessToken['expires'])
        ) {
            return new AccessToken([
                'access_token' => $accessToken['accessToken'],
                'refresh_token' => $accessToken['refreshToken'],
                'expires' => $accessToken['expires'],
                'baseDomain' => $accessToken['baseDomain'],
            ]);
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }

    public function checkExpires(): bool
    {
        if (file_exists(TOKEN_FILE)) {
            $accessToken = $this->getAccessToken();
            if (!$accessToken->hasExpired()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
