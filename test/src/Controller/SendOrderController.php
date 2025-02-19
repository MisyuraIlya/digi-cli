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
use App\Repository\NotificationUserRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\OneSignal;
use App\Service\SmsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Exception\InvalidArgumentException;
use Symfony\Component\Routing\Annotation\Route;

class SendOrderController extends AbstractController
{

    public function __construct(
        private HistoryRepository $historyRepository,
        private HistoryDetailedRepository $historyDetailedRepository,
        private UserRepository $userRepository,
        private ProductRepository $productRepository,
        private readonly ErpManager $erpManager,
        private readonly OneSignal $oneSignal,
        private readonly NotificationRepository $notificationRepository,
        private readonly NotificationUserRepository $notificationUserRepository,
        private readonly SmsHandler $smsHandler
    )
    {
        $this->obligoBlock = $_ENV['OBLIGO_BLOCK'] === 'true';
    }

    #[Route('/sendOrder', name: 'send_order', methods: ['POST'])]
    public function index(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $cart = $data['cart'] ?? null;
            $comment = $data['comment'] ?? null;
            $user = $data['user'] ?? null;
            $discountUser = (float) $data['discountUser'] ?? null;
            $deliveryPrice = $data['deliveryPrice'] ?? null;
            $deliveryDate = $data['deliveryDate'] ?? null;
            $agent = $data['agent'] ?? null;
            $type = $data['documentType'] ?? null;
            $isSendToErp = $data['isSendToErp'] ?? null;
            $total = $data['total'] ?? null;
            $address = $data['address'] ?? null;
            $city = $data['city'] ?? null;

            $findUser = $this->userRepository->findOneByExIdAndPhone($user['extId'], $user['phone']);
            if(!$findUser) throw new \Exception('לא נמצא לקוח כזה');
            if(!$total) throw new \Exception('no total set');
            if($findUser->getIsBlocked()) throw new \Exception('לקוח חסום אנא פנה לתמיכה');
            if(!$type === null) throw new \Exception('documentType שדה חובה order|quote|return');
            if(!$deliveryDate) throw new \Exception('לא נבחר יום הספקה');
            if(count($cart) == 0) throw new \Exception('לא נבחר שום מוצר');

            if($agent){
                $findAgent = $this->userRepository->findOneById($agent['id']);
            } else {
                $findAgent = null;
            }
            $dateTimeImmutable = \DateTimeImmutable::createFromFormat('Y-m-d', $deliveryDate);
            $history = $this->HandleHistory(
                $total,
                $findUser,
                $comment,
                $type,
                $deliveryPrice,
                $dateTimeImmutable,
                $discountUser,
                $request->getContent(),
                $address,
                $city,
                $findAgent
            );

            $this->HandleHistoryDetailed($history, $cart);

            $orderNumber = null;
            if($this->obligoBlock){
                if($findUser->getMaxObligo() < $total && $type === 'order'){
                    $history->setDocumentType($this->getDocumentTypeFromString($type));
                    $history->setOrderStatus(PurchaseStatus::WAITING_APPROVE);
                    $history->setError('לא מספיק אובליגו');
                    $this->historyRepository->save($history,true);
                    return  $this->json((new ApiResponse('',"לא מספיק אובליגו"))->OnError());
                }
            }

            if($isSendToErp){
                $orderNumber = $this->erpManager->SendOrder($history);
            }
            if($orderNumber && $isSendToErp){
                $history->setOrderExtId($orderNumber);
                $history->setOrderStatus(PurchaseStatus::PAID);
                $this->historyRepository->save($history,true);
                $userName = $findUser->getExtId() . ' ' . $findUser->getName();

                try {
                    $this->oneSignal
                        ->SendOrderPush($findUser, 'התקבלה הזמנה במערכת', "מספר הזמנה: $orderNumber")
                        ->AlertToAgentsGetOrder('בוצעה הזמנה במערכת',"לקורח $userName ביצעה הזמנה $orderNumber");
                    $this->CreateNotification($findUser, $orderNumber);
                    $this->smsHandler->SendSms($findUser->getPhone(), "התקבלה הזמנה במערכת, מספר הזמנה$orderNumber");
                } catch (\Exception $exception){

                }

                $obj = new \stdClass();
                $obj->historyId = $history->getId();
                $obj->orderNumber = $orderNumber;
                return  $this->json((new ApiResponse($obj,''))->OnSuccess());
            } else if(!$isSendToErp) {
                $obj = new \stdClass();
                $obj->historyId = $history->getId();
                $obj->orderNumber = null;
                return  $this->json((new ApiResponse($obj,''))->OnSuccess());
            } else {
                throw new \Exception('הזמנה לא שודרה: לא התקבל מספר הזמנה');
            }

        } catch (\Exception $e) {
                $data = json_decode($request->getContent(), true);
                $type = $data['documentType'];
                $comment = $data['comment'];
                $user = $data['user'];
                $findUser = $this->userRepository->findOneByExIdAndPhone($user['extId'], $user['phone']);
                if(!empty($history)){
                    $history->setCreatedAt(new \DateTimeImmutable());
                    $history->setUpdatedAt(new \DateTimeImmutable());
                    $history->setError($e->getMessage());
                    $history->setJson($request->getContent());
                    $history->setIsSendErp(false);
                    $history->setOrderStatus(PurchaseStatus::FAILED);
                    $history->setDocumentType($this->getDocumentTypeFromString($type));
                    $history->setIsBuyByCreditCard(false);
                    $history->setOrderComment($comment);
                    $history->setUser($findUser);
                    $this->historyRepository->save($history, true);
                }
            return $this->json((new ApiResponse(null,$e->getMessage()))->OnError());
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

    private function HandleHistory(
        float $total,
        User $user,
        string $comment,
        $type ,
        int $deliveryPrice,
        \DateTimeImmutable $deliveryDate,
        int $discountUser,
        string $json,
        string $address,
        string $city,
        ?User $agent
    )
    {

        $newHistory = new History();
        $newHistory->setUser($user);
        $newHistory->setCreatedAt(new \DateTimeImmutable());
        $newHistory->setUpdatedAt(new \DateTimeImmutable());
        $newHistory->setDiscount($discountUser);
        $newHistory->setDeliveryDate($deliveryDate);
        $newHistory->setOrderComment($comment);
        $newHistory->setDeliveryPrice($deliveryPrice);
        $newHistory->setTotal($total);
        $newHistory->setAddress($address);
        $newHistory->setCity($city);
        $newHistory->setOrderStatus(PurchaseStatus::PENDING);
        $newHistory->setDocumentType($this->getDocumentTypeFromString($type));
        $newHistory->setJson($json);
        if($agent){
            $newHistory->setAgent($agent);
            $newHistory->setIsSendErp($agent->isIsAllowOrder());
        } else {
            $newHistory->setIsSendErp(true);
        }
        $newHistory->setIsBuyByCreditCard(false);
        $historyId = $this->historyRepository->save($newHistory, true);
        return $historyId;
    }

    private function HandleHistoryDetailed(History $history, array $cart)
    {
        foreach ($cart as $itemRec){
            $findProduct = $this->productRepository->findOneBySku($itemRec['sku']);
            if(!$findProduct) throw new \Error('לא נמצא פריט כזה');
            if(!$findProduct->isIsPublished()) throw new \Error( 'פריט חסום' . ' ' .  $findProduct->getTitle());
            $obj = new HistoryDetailed();
            $obj->setProduct($findProduct);
            $obj->setHistory($history);
            $obj->setQuantity($itemRec['quantity']);
            $obj->setTotal($itemRec['total']);
            $obj->setSinglePrice($itemRec['price']);
            $obj->setDiscount($itemRec['discount']);
            $this->historyDetailedRepository->createHistoryDetailed($obj,true);
            $history->addHistoryDetailed($obj);
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


}
