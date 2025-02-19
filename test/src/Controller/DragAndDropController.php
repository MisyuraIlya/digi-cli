<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\HomeEdit;
use App\helpers\ApiResponse;
use App\Repository\CategoryRepository;
use App\Repository\HomeEditRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DragAndDropController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly HomeEditRepository $homeEditRepository,
    )
    {
    }

    #[Route('/dragAndDrop/categories', name: 'drag_and_drop_categories', methods: ['POST'])]
    public function categories(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            foreach ($data as $itemRec){
                $find = $this->categoryRepository->findOneById($itemRec['id']);
                assert($find instanceof Category);
                if($find){
                    $orden = $itemRec['orden'] ?? 0;
                    $find->setOrden($orden);
                    $this->categoryRepository->createCategory($find, true);
                }
            }
            return $this->json((new ApiResponse('ok',"נשלח קוד סודי לאימות"))->OnSuccess());
        } catch (\Exception $e) {
            return $this->json((new ApiResponse(null, $e->getMessage()))->OnError());
        }
    }

    #[Route('/dragAndDrop/homeEdit', name: 'drag_and_drop_home', methods: ['POST'])]
    public function home(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            foreach ($data as $itemRec){
                $find = $this->homeEditRepository->findOneById($itemRec['id']);
                assert($find instanceof HomeEdit);
                if($find){
                    $orden = $itemRec['orden'] ?? 0;
                    $find->setOrden($orden);
                    $this->homeEditRepository->save($find, true);
                }
            }
            return $this->json((new ApiResponse('ok',"נשלח קוד סודי לאימות"))->OnSuccess());
        } catch (\Exception $e) {
            return $this->json((new ApiResponse(null, $e->getMessage()))->OnError());
        }
    }
}
