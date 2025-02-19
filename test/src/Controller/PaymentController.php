<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Enum\PurchaseStatus;
use App\Repository\HistoryRepository;
use App\Repository\PaymentRepository;
use App\Repository\UserRepository;
use App\Service\MaxApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{

    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly UserRepository $userRepository,
        private readonly HistoryRepository $historyRepository,
    )
    {
        $this->paymentMethod = $_ENV['PAYMENT_SYSTEM'];
        $this->successUrl = $_ENV['SUCCESS_LINK'];
        $this->errorUrl = $_ENV['ERROR_LINK'];
        $this->MASOF = $_ENV['MASOF'];
    }

    #[Route('/payment/success', name: 'app_payment_success', methods: ['GET','POST'])]
    public function success(Request $request): Response
    {

        $requestData = $request->getContent();
        $orderId = null;
        $sum = 0;
        $this->saveRequestData('succes'.$requestData);

        if($this->paymentMethod === 'yadsarig'){
            $parsedData = [];
            parse_str($requestData, $parsedData);
            $orderId = $parsedData['hID'] ?? null;
            $sum = $parsedData['sum'] ?? 0;
        }

        if($this->paymentMethod === 'tranzilla'){
            $parsedData = [];
            parse_str($requestData, $parsedData);
            $orderId = $parsedData['hID'] ?? null;
            $sum = $parsedData['sum'] ?? 0;

        }

        if($orderId && $sum) {
            $findHid = $this->historyRepository->findOneBy(['id' => $orderId]);
            $findHid->setOrderStatus(PurchaseStatus::PAID);
            $findHid->setIsBuyByCreditCard(true);
            $this->historyRepository->save($findHid);

            $payment = new Payment();
            $payment->setJson(json_encode($parsedData));
            $payment->setAmount($sum);
            $payment->setUser($findHid->getUser());
            $this->paymentRepository->save($payment,true);
            $txt = new \stdClass();
            $txt->message = 'שודר בהצלחה';

            $script = sprintf("
            <script>
                window.parent.postMessage({res: 'SuccessPayment', data: %s}, '*');
            </script>
        ", json_encode($txt));

            return new Response($script, Response::HTTP_OK);
        } else {
            $txt = new \stdClass();
            $txt->message = 'שגיאה';
            $script = sprintf("
            <script>
                window.parent.postMessage({res: 'ErrorPayment', data: %s}, '*');
            </script>
        ", json_encode($txt));

            return new Response($script, Response::HTTP_OK);
        }

    }

    #[Route('/payment/error', name: 'app_payment_error', methods: ['GET','POST'])]
    public function error(Request $request): Response
    {

        $requestData = $request->getContent();
        $this->saveRequestData('error'.$requestData);

        if($this->paymentMethod === 'yadsarig'){

        }

        if($this->paymentMethod === 'tranzilla'){
            $parsedData = [];
            parse_str($requestData, $parsedData);
            $orderId = $parsedData['hID'] ?? null;
            if($orderId){
                $findHid = $this->historyRepository->findOneBy(['id' => $orderId]);
                if($findHid){
                    $findHid->setOrderStatus(PurchaseStatus::FAILED);
                    $this->historyRepository->save($findHid);
                }
            }

        }

        $txt = new \stdClass();
        $txt->message = 'שגיאה בעמצאי תשלום';

        $script = sprintf("
            <script>
                window.parent.postMessage({res: 'ErrorPayment', data: %s}, '*');
            </script>
        ", json_encode($txt));

        return new Response($script, Response::HTTP_OK);
    }

    private function saveRequestData(string $requestData): void
    {
        file_put_contents(__DIR__.'/../../public/paymentlog.txt', $requestData . PHP_EOL, FILE_APPEND);
    }

    #[Route('/payment/generateIframe', name: 'generate_iframe', methods: ['POST'])]
    public function generateIframe(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $orderId = $data['orderId'];
        $total = $data['total'];
        $userId = $data['userId'];
        $user = $this->userRepository->findOneById($userId);

        if($this->paymentMethod === 'yadsarig') {
            $iframeUrl = (new MaxApi())->generateUrl($total,$orderId,$user);
        }

        if($this->paymentMethod === 'tranzilla'){
            $iframeUrl = "
            https://direct.tranzila.com/$this->MASOF/iframenew.php?success_url_address=".$this->successUrl."&fail_url_address=".$this->errorUrl.
                    "&currency=1&lang=il&cred_type=1&sum=".$total."&u_i=".$userId."&hID=$orderId
            ";
        }

        return new Response($iframeUrl, Response::HTTP_OK);
    }


}
