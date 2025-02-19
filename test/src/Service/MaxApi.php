<?php

namespace App\Service;

use App\Entity\User;

class MaxApi
{
    public function __construct()
    {
        $this->url = 'https://icom.yaad.net';
        $this->Masof = $_ENV['MASOF'];
        $this->KEY = $_ENV['YAD_KEY'];
        $this->PassP = $_ENV['PASSP'];
        $this->Tash = 1;
    }


    function generateUrl(
        float $amount,
        string $orderId,
        User $user

    )
    {

        $params = new \stdClass;
        $params->action = 'APISign';
        $params->Info = 'j5 verification transaction';
        $params->Amount = $amount;
        $params->Order = $orderId;
        $params->email = '';
        $params->ClientName = $user->getName();
        $params->ClientLName = '';
        $params->street = '';
        $params->city = '';
        $params->zip = '';
        $params->cell = '';
        $params->UserId = '';
        $params->Sign = 'True';
        $params->MoreData = 'True';
        $params->tmp = 5;
        $params->Masof = $this->Masof;
        $params->PassP = $this->PassP;
        $params->PageLang = 'HEB';
        $params->UTF8 = 'True';
        $params->Coin = 1;
        $params->J5 = 'True';
        $params->Tash = $this->Tash;
        $params->What = 'SIGN';
        $params->KEY = $this->KEY;
        $params->Fild1 = "";
        $params->Fild2 = base64_encode( serialize($orderId) );
        $params->Fild3 = "";
        //$params->Hesh = "";
//        $params->heshDesc = '[0~TBON~0.3~60][0~Item 2~2~1]';
        $func = '/cgi-bin/yaadpay/yaadpay3ds.pl?';

        $result = $this->ajaxGet($params,$func);

        return $this->url.$func.$result;

    }

    function GetToken($transaction_id): string
    {
        $fields = array(
            'action' 	  => 'getToken',
            'Masof'	   	=>	$this->Masof,
            'PassP'     => $this->PassP,
            'TransId' 	=> $transaction_id,
        );

        $func = '/p/?';

        $result = $this->ajax($fields, $func);
        parse_str($result, $output);
        return $output['Token'] ?? '';

    }

    function J5Payment($data)
    {

        $params = new \stdClass;
        $params->action = 'soft';
        $params->Masof = $this->Masof;
        $params->PassP = $this->PassP;
        $params->Amount = $data->Amount;
        $params->ACode = $data->Acode;
        $params->CC = $data->token;
        $params->Tmonth = $data->Tmonth;
        $params->Tyear = $data->Tyear;
        $params->Coin = $data->coin;
        $params->Info = 'הזמנה' . ' ' . $data->clientName;
        $params->Order = $data->Order;
        $params->Tash = '1';
        $params->UserId = $data->UserId;
        $params->ClientLName = $data->clientName;
        $params->ClientName = '';
        $params->cell = $data->cell;
        $params->email = $data->email;
        $params->street = $data->street;
        $params->city = $data->city;
        $params->zip = '';
        $params->J5 = 'False';
        $params->MoreData = 'True';
        $params->Postpone = 'False';
        $params->UTF8 = 'True';
        $params->UTF8out = 'True';
        $params->Token = 'True';
        $params->Pritim = 'True';
        $params->heshDesc = $data->heshDesc;
//        if($data->Payments > 1) {
//            $params->TashFirstPayment = $data->firstPayment;
//        }
        $func = '/p3/?';
        $result = $this->ajaxGet($params, $func);
        parse_str($result, $output);
        $response = new \stdClass;
        if (strpos($this->removeNewLines($output['errMsg']), '(0)') !== false) {
            $response->result = 'success';
            $response->data = $output;
        } else {
            $response->result = 'error';
            $response->code = $output['errMsg'];
        }
        return $response;
    }

    function ajaxGet($params, $func)
    {

        $logData = date('Y-m-d H:i:s') . ' - ' . $this->url.$func.http_build_query($params) . PHP_EOL;
        file_put_contents('resPay.txt', $logData, FILE_APPEND);
//        dd($this->url.$func.http_build_query($params));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$func.http_build_query($params));
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        return $result;

    }

    function ajax($params, $func)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$func);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        return $result;

    }

    function removeNewLines($text)
    {
        return trim(preg_replace('/\s+/', '', $text));
    }

    function createPdf($transId)
    {
        $params = new \stdClass;
        $params->Masof = $this->Masof;
        $params->action = 'APISign';
        $params->KEY = $this->KEY;
        $params->What = 'SIGN';
        $params->PassP = $this->PassP;
        $params->TransId = $transId;
        $params->type = 'EZCOUNT';
        $params->ACTION = 'PrintHesh';
        $params->SendHesh = 'True';
        $func = '/cgi-bin/yaadpay/yaadpay3ds.pl?';
        $result = $this->ajaxGet($params,$func);
        $pdfLink = "https://icom.yaad.net/cgi-bin/yaadpay/yaadpay3ds.pl?$result";
        return $pdfLink;

    }


}