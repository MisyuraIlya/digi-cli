<?php

namespace App\Controller;

use App\Erp\Core\ErpManager;
use App\helpers\ApiResponse;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    public function __construct(
        private readonly ErpManager $erpManager,
        private readonly UserRepository $userRepository,
    )
    {
    }

    #[Route('/cartCheck', name: 'app_cart', methods: ['POST'])]
    public function index(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->userRepository->findOneBy(['extId' => $data['user']['extId']]);
            $vatEnabled = true;
            if($user){
                $vatEnabled = $user->isIsVatEnabled();
            }
            $obj = new \stdClass();
            include('../../mainGlobals.php');
            $obj->maam = $vatEnabled ? json_decode((new \mainGlobals())->mainGlobals())->mainGlobals->maamPerc : 0;

            return  $this->json((new ApiResponse($obj,''))->OnSuccess());
        } catch (\Exception $e) {
            return $this->json((new ApiResponse(null,$e->getMessage()))->OnError());
        }
    }

    private function GetTwoWeeksAhead($deliveryData)
    {
        $weekDays = [
            '1' => 'Sunday',
            '2' => 'Monday',
            '3' => 'Tuesday',
            '4' => 'Wednesday',
            '5' => 'Thursday',
            '6' => 'Friday',
            '7' => 'Saturday'
        ];

        $uniqueDeliveryData = [];
        $seenCombinations = [];

        foreach ($deliveryData as &$data) {
            $weekDay = $data->weekDay;
            $targetDay = $weekDays[$weekDay];

            $dateForWeekDay = new \DateTime();

            if ($dateForWeekDay->format('l') == $targetDay) {
                $nextTargetDay = $dateForWeekDay;
            } else {
                $nextTargetDay = new \DateTime();
                $nextTargetDay->modify('next ' . $targetDay);
            }

            $nextTargetDay->setTime(intval($data->hour / 100), $data->hour % 100);

            $areaCityCombination = $data->area . '-' . $data->city;

            if (!isset($seenCombinations[$areaCityCombination])) {
                $seenCombinations[$areaCityCombination] = true;

                $currentDelivery = clone $data;
                $currentDelivery->deliveryDate = $nextTargetDay->format('Y-m-d');
                $uniqueDeliveryData[] = $currentDelivery;

                $nextDeliveryDate = clone $nextTargetDay;
                $nextDeliveryDate->modify('+7 days');

                $nextDelivery = clone $data;
                $nextDelivery->deliveryDate = $nextDeliveryDate->format('Y-m-d');
                $uniqueDeliveryData[] = $nextDelivery;
            }
        }

        return $uniqueDeliveryData;

    }

    private function checkStock($data)
    {

    }

    private function checkPrice($data)
    {

    }


}
