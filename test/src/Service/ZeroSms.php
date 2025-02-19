<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ZeroSms
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function sendSms($mobile, $text)
    {
        $userName = 'statosbiz';
        $pass = 'DigiSms!@#$1';
        $url = "https://new.019sms.co.il/api";
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
          <sms>
          <user>
          <username>' . $userName . '</username>
          <password>' . $pass . '</password>
          </user>
          <source>0584018761</source>
          <destinations>
          <phone>' . $mobile . '</phone>
          </destinations>
          <message>' . $text . '</message>
          <add_dynamic>0</add_dynamic>
          <response>0</response>
          </sms>';

        try {
            $response = $this->httpClient->request(
                'POST',
                $url,
                [
                    'headers' => [
                        'Content-Type' => 'text/xml; charset=utf-8',
                    ],
                    'body' => $xml,
                    'timeout' => 60, // Set a longer timeout (in seconds)
                     'verify_peer' => false, // Only use if SSL verification is causing issues (not recommended for production)
                     'verify_host' => false,
                ]
            );

            $statusCode = $response->getStatusCode();
            $content = $response->getContent();
            return $content;
        } catch (\Exception $e) {
            return null;
        }
    }
}
