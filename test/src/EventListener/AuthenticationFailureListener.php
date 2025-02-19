<?php

namespace App\EventListener;
use App\helpers\ApiResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthenticationFailureListener
{
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $response = new JsonResponse((new ApiResponse(null, 'לא נמצא לקוח'))->OnError(), 200);
        $event->setResponse($response);
    }
}