<?php

namespace App\Controller;

use App\Entity\History;
use App\Entity\HistoryDetailed;
use App\Entity\Notification;
use App\Entity\NotificationUser;
use App\Entity\User;
use App\Enum\HistoryDocumentTypeEnum;
use App\Enum\PurchaseStatus;
use App\Erp\Core\ErpManager;
use App\helpers\ApiResponse;
use App\Repository\HistoryDetailedRepository;
use App\Repository\HistoryRepository;
use App\Repository\NotificationRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\OneSignal;
use App\Service\PriceHandler;
use App\Service\SmsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Exception\InvalidArgumentException;
use Symfony\Component\Routing\Annotation\Route;

class OfflineController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ProductRepository $productRepository,
        private readonly HistoryRepository $historyRepository,
        private readonly HistoryDetailedRepository $historyDetailedRepository,
        private readonly ErpManager $erpManager,
        private readonly PriceHandler $priceHandler,
        private readonly NotificationRepository $notificationRepository,
        private readonly OneSignal $oneSignal,
        private readonly SmsHandler $smsHandler
    )
    {
    }

    #[Route('/offline/handlePrice', name: 'offline_price', methods: ['POST'])]
    public function handlePrice(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $history = $data['history'] ?? null;
        $historyDetailed = $data['historyDetailed'] ?? null;

        $user = $this->userRepository->findOneById($history['user']['id']);
        $vatEnabled = true;
        if($user){
            $vatEnabled = $user->isIsVatEnabled();
        }
        include('../../mainGlobals.php');
        $tax = $vatEnabled ? json_decode((new \mainGlobals())->mainGlobals())->mainGlobals->maamPerc : 0;
        $products = [];
        foreach ($historyDetailed as $itemRec){
            $product =$this->productRepository->findOneBy(['sku' => $itemRec['sku']]);
            if($product){
                $products[] = $product;
            }
        }
        $this->priceHandler->HandlePriceArray($user,$products);
        $totalPrice = 0;
        foreach ($products as $product){
            foreach ($historyDetailed as &$itemRec){
                if($itemRec['sku'] === $product->getSku()){
                    $itemRec['priceByOne'] = $product->getFinalPrice();
                    $total = $product->getFinalPrice() * $itemRec['quantity'];
                    $itemRec['total'] = $total;
                    $totalPrice += $total;
                    $itemRec['product']['finalPrice'] = $product->getFinalPrice();
                }
            }
        }
        $history['total'] = $totalPrice;
        $data = [
            'history' => $history,
            'historyDetailed' => $historyDetailed,
            'tax' => $tax,
        ];
        return $this->json($data);
    }

    #[Route('/offline/sendOrder', name: 'offline_send_order', methods: ['POST'])]
    public function handleSendOrder(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $history = $data['history'] ?? null;
            $historyDetailed = $data['historyDetailed'] ?? null;
            $user = $this->userRepository->findOneById($history['user']['id']);
            $agent = $this->userRepository->findOneBy(['extId' => $history['userExId'], 'isAgent' => true]);
            $history = $this->HandleHistory($history, $user, $agent, $request->getContent());
            if ($history) {
                foreach ($historyDetailed as $itemRec) {
                    $product = $this->productRepository->findOneBy(['sku' => $itemRec['sku']]);
                    if ($product) {
                        $detailed = new HistoryDetailed();
                        $detailed->setHistory($history);
                        $detailed->setProduct($product);
                        $detailed->setQuantity($itemRec['quantity']);
                        $detailed->setTotal($itemRec['total']);
                        $detailed->setDiscount($itemRec['discount']);
                        $detailed->setSinglePrice($itemRec['priceByOne']);
                        $this->historyDetailedRepository->createHistoryDetailed($detailed, true);
                    }
                }
            }

            $orderNumber = $this->erpManager->SendOrder($history);

            if ($orderNumber) {
                $history->setOrderExtId($orderNumber);
                $history->setOrderStatus(PurchaseStatus::PAID);
                $this->historyRepository->save($history, true);
                $userName = $user->getExtId() . ' ' . $user->getName();

                try {
                    $this->oneSignal
                        ->SendOrderPush($user, 'התקבלה הזמנה במערכת', "מספר הזמנה: $orderNumber")
                        ->AlertToAgentsGetOrder('בוצעה הזמנה במערכת', "לקורח $userName ביצעה הזמנה $orderNumber");
                    $this->CreateNotification($user, $orderNumber);
                    $this->smsHandler->SendSms($user->getPhone(), "התקבלה הזמנה במערכת, מספר הזמנה$orderNumber");
                } catch (\Exception $exception) {

                }

                $obj = new \stdClass();
                $obj->historyId = $history->getId();
                $obj->orderNumber = $orderNumber;
                return $this->json((new ApiResponse($obj, ''))->OnSuccess());
            } else {
                throw new \Exception('הזמנה לא שודרה: לא התקבל מספר הזמנה');
            }
        } catch (\Exception $e) {
            $history = $data['history'] ?? null;
            $user = $this->userRepository->findOneById($history['user']['id']);
            if(!empty($history)){
                $history->setCreatedAt(new \DateTimeImmutable());
                $history->setUpdatedAt(new \DateTimeImmutable());
                $history->setError($e->getMessage());
                $history->setJson($request->getContent());
                $history->setIsSendErp(false);
                $history->setOrderStatus(PurchaseStatus::FAILED);
                $history->setIsBuyByCreditCard(false);
                $history->setUser($user);
                $this->historyRepository->save($history, true);
            }
            return $this->json((new ApiResponse(null,$e->getMessage()))->OnError());
        }
    }

    private function CreateNotification(User $user, $orderNumber)
    {
        $description = "התקבלה הזמנה מספר" . ' ' .  "$orderNumber";
        $title = "התקבלה הזמנה";
        $createNot = new Notification();
        $createNot->setCreatedAt(new \DateTimeImmutable());
        $createNot->setTitle($title);
        $createNot->setDescription($description);
        $createNot->setIsSend(true);
        $createNot->setIsPublic(false);
        $createNot->setIsPublished(true);
        $createNot->setIsSystem(true);
        $this->notificationRepository->save($createNot,true);

        $userNot = new NotificationUser();
        $userNot->setUser($user);
        $userNot->setNotification($createNot);
        $userNot->setIsRead(false);
        $userNot->setCreatedAt(new \DateTimeImmutable());
        $this->notificationUserRepository->save($userNot,true);

    }

    private function HandleHistory($history, ?User $user, ?User $agent ,$data): History
    {
        if($user){
            $historyRep = new History();
            $historyRep->setUser($user);
            if($agent){
                $historyRep->setAgent($agent);
            }
            $historyRep->setCreatedAt(new \DateTimeImmutable());
            $historyRep->setUpdatedAt(new \DateTimeImmutable());
            $historyRep->setDiscount(0);
            $historyRep->setDeliveryDate(new \DateTimeImmutable($history['deliveryAt']));
            $historyRep->setOrderComment($history['orderComment']);
            $historyRep->setTotal($history['total']);
            $historyRep->setOrderStatus(PurchaseStatus::PENDING);
            $historyRep->setDocumentType($this->getDocumentTypeFromString($history['documentType']));
            $historyRep->setJson($data);
            $historyRep->setIsSendErp(true);
            $historyRep->setIsBuyByCreditCard(true);
            $this->historyRepository->save($historyRep,true);
            return $historyRep;
        }

    }

    public function getDocumentTypeFromString(string $type): ?HistoryDocumentTypeEnum
    {
        $type = strtoupper($type);

        $documentType = HistoryDocumentTypeEnum::tryFrom($type);

        if (!$documentType) {
            throw new InvalidArgumentException("Invalid document type: $type");
        }

        return $documentType;
    }

}