<?php
namespace Luxoft\GA;

abstract class AbstractClient
{

    protected $client;
    protected $viewId;

    public function __construct($clientEmail, $privateKey, $viewId)
    {
        $this->viewId = $viewId;

        $scopes = ['https://www.googleapis.com/auth/analytics.readonly'];
        $credentials = new \Google_Auth_AssertionCredentials(
            $clientEmail,
            $scopes,
            $privateKey,
            'notasecret'
        );

        $this->client = new \Google_Client();
        $this->client->setAssertionCredentials($credentials);
    }

    /**
     * Returns authorized Google_Client instance
     *
     * @return \Google_Client
     */
    protected function getClient()
    {
        if ($this->client->getAuth()->isAccessTokenExpired()) {
            $this->client->getAuth()->refreshTokenWithAssertion();
        }

        return $this->client;

    }
}
