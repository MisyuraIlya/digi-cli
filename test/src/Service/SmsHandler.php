<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SmsHandler
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    )
    {
        $this->type = $_ENV['SMS_CENTER'];
        $this->title = $_ENV['TITLE'];
    }

    public function SendSms(string $phone, string $description)
    {
        if($this->type === 'activeTrail'){
            return (new ActiveTrail($this->httpClient))->SMS($phone,$this->title,$description);
        } else if($this->type === 'flashy') {
            return (new Flashy());
        } else if($this->type === 'informu') {

        } else if($this->type === 'zeroSms') {
            return (new ZeroSms($this->httpClient))->sendSms($phone,$this->title);
        } else {
            throw new \Exception('[ERROR]: SMS CENTER NOT EXIST');
        }
    }
}