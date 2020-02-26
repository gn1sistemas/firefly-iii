<?php
/**
 * ProsocioProvider.php
 * Copyright (c) 2019 anderson.silva@gn1.com.br
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */
declare (strict_types = 1);


namespace FireflyIII\Providers;


use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class ProsocioProvider extends AbstractProvider implements ProviderInterface
{

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(env('PROSOCIO_URL_AUTHORIZE'), $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return env('PROSOCIO_URL_ACCESS_TOKEN');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret)],
            'body' => $this->getTokenFields($code),
        ]);

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_add(
            parent::getTokenFields($code), 'grant_type', 'authorization_code'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(env('PROSOCIO_URL_USER'), [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $res = $response->getBody();

        return json_decode((string)$res, true);
    }


    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => $user['login'],
            'name' => $user['name']
        ]);
    }

}
