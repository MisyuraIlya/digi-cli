<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ActiveTrail
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    )
    {
        $this->token = $_ENV['ACTIVE_TRAIL_TOKEN'];
    }

    public function SMS($phone,$title,$description)
    {
        $obj = new \stdClass();
        $obj->details = new \stdClass();
        $obj->details->name = $title;
        $obj->details->from_name = $title;
        $obj->details->content = $description;

        $obj->scheduling = new \stdClass();
        $obj->scheduling->send_now = true;

        $obj->mobiles = array();
        $mobile = new \stdClass();
        $mobile->phone_number = $phone;
        $obj->mobiles[] = $mobile;

        $json = json_encode($obj);

        $response = $this->httpClient->request(
            'POST',
           "https://webapi.mymarketing.co.il/api/smscampaign/OperationalMessage?Token={$this->token}",
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => $json
            ]
        );
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = $response->toArray();
        return $content;
    }
}