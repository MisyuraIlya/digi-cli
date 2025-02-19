<?php

namespace App\Controller;

use App\Entity\AgentObjective;
use App\Enum\AgentObjectiveType;
use App\helpers\ApiResponse;
use App\Repository\AgentObjectiveRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyScheduleCalendarController extends AbstractController
{

    private $agendId;
    private $weekFrom;
    private $weekTo;

    public function __construct(
        private readonly AgentObjectiveRepository $agentObjectiveRepository,
    )
    {
    }

    #[Route('/api/calendar/{agentId}/{weekFrom}/{weekTo}', name: 'app_my_schedule_calendar')]
    public function calendarInfo(Request $request, $agentId, $weekFrom, $weekTo): Response
    {
        $this->agendId = $agentId;
        $this->weekFrom = $weekFrom;
        $this->weekTo = $weekTo;
        $response = $this->agentObjectiveRepository->calendarObjective($agentId,$weekFrom,$weekTo);
        $res = $this->GroupByDays($response);
        $newArr = [];
        foreach ($res as $key => $dayRec){
            $res2 = $this->MergedOverLappedTimes($dayRec);
            $newArr[] = $res2;
        }

        $flattened = $this->flattenArray($newArr);
        return $this->json((new ApiResponse($flattened,"נשלח קוד סודי לאימות"))->OnSuccess());

    }

    private function GroupByDays($data)
    {
        $groupedEvents = [];
        foreach ($data as $event) {
            assert($event instanceof AgentObjective);
            $dayOfWeek = $event->getChoosedDay();
            if (!isset($groupedEvents[$dayOfWeek])) {
                $groupedEvents[$dayOfWeek] = [];
            }
            $groupedEvents[$dayOfWeek][] = $event;
        }
        return $groupedEvents;
    }

    private function MergedOverLappedTimes(array $events)
    {
//        assert($events instanceof  AgentObjective);

        usort($events, function($a, $b) {
            return $a->getHourFrom() <=> $b->getHourTo();
        });

        $merged_events = array();
        $current_event = null;

        foreach ($events as $event) {
            assert($event instanceof AgentObjective);
            if (!$current_event) {
                $current_event = $event;
            } else {
                if ($current_event->getHourTo() > $event->getHourFrom()) {
                    $current_event->setHourTo(max($current_event->getHourTo(), $event->getHourTo()));
                    $current_event->setObjectiveType(AgentObjectiveType::MIX);
                } else {
                    $merged_events[] = $current_event;
                    $current_event = $event;
                }
            }
        }
        if ($current_event) {
            $merged_events[] = $current_event;
        }
        foreach ($merged_events as &$itemRec){
            if($itemRec->getObjectiveType() == AgentObjectiveType::MIX){
                $multiple = $this->agentObjectiveRepository->findMissionsByAgentIdAndDateTimeRange($this->agendId, $this->weekFrom,$this->weekTo, $itemRec->getHourFrom(), $itemRec->getHourTo());
                $itemRec->setSubStuck($multiple);
            }
        }


        return $merged_events;
    }

    private function flattenArray(array $array) {
        $result = array();

        foreach ($array as $element) {
            if (is_array($element)) {
                $result = array_merge($result, $this->flattenArray($element));
            } else {
                $result[] = $element;
            }
        }
        return $result;
    }

}
