<?php

namespace App\Controller;

use App\Enum\CatalogDocumentTypeEnum;
use App\Erp\Core\ErpManager;
use App\Repository\AttributeMainRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\PriceHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\Doctrine\Orm\Paginator;

class CatalogController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly ProductRepository $productRepository,
        private readonly AttributeMainRepository $attributeMainRepository,
        private readonly ErpManager $erpManager,
        private readonly UserRepository $userRepository,
        private readonly PriceHandler $priceHandler,
    )
    {}

    #[Route('/catalog/{documentType}/{lvl1}/{lvl2}/{lvl3}', name: 'catalog', methods: ['GET'])]
    public function success(Request $request,$documentType,$lvl1,$lvl2,$lvl3): Response
    {

        $page = $request->query->get('page',1);
        $userId = $request->query->get('userId');
        $userEntity = null;
        if($userId){
            $userEntity = $this->userRepository->findParentUser($userId);
        }
        $mode = $request->query->get('mode');
        $itemsPerPage = $request->query->get('itemsPerPage', 24);
        $orderBy = $request->query->get('orderBy');
        $search = $request->query->get('search');

        $filters = $this->ParseFilter($request);

        $skusForSearch = [];

        if($documentType == CatalogDocumentTypeEnum::SPECIAL->value && $userEntity){
            $skusForSearch = $this->HandleSpecial($userEntity->getExtId());
            if(empty($skusForSearch)){
                $skusForSearch = ['1'];
            }
        }

        if($documentType ==  CatalogDocumentTypeEnum::NEW->value && $userEntity) {
            $skusForSearch = $this->HandleNewProducts($userEntity->getExtId());
            if(empty($skusForSearch)){
                $skusForSearch = ['1'];
            }
        }

        if($documentType ==  CatalogDocumentTypeEnum::NOT_BUY->value && $userEntity) {
            $skusForSearch = $this->HandleProductsImNotBuy($userEntity->getExtId());
            if(empty($skusForSearch)){
                $skusForSearch = ['1'];
            }
        }

        if($documentType ==  CatalogDocumentTypeEnum::IM_BUY->value && $userEntity) {
            $skusForSearch = $this->HandleProductsImBuy($userEntity->getExtId());
            if(empty($skusForSearch)){
                $skusForSearch = ['1'];
            }
        }
        $data = $this->productRepository->getCatalog(
            $page,
            $itemsPerPage,
            (int) $lvl1,
            (int) $lvl2,
            (int) $lvl3,
            false,
            $orderBy,
            $filters,
            $search,
            $skusForSearch,
        );
        $this->priceHandler->HandlePrice($userEntity,$data);
        $this->stockChecker->StockHandler($data);
        if($userEntity){
            $this->HandleBonuses($data,$userEntity);
        }
        //FILTER
        $allItems = $this->productRepository->getAllCatalog(
            (int) $lvl1,
            (int) $lvl2,
            (int) $lvl3,
            false, // Adjust as needed
            $orderBy,
            $filters,
            $search,
            $skusForSearch
        );
        $allSkus = array_map(fn($item) => $item->getSku(), $allItems);
        $filter = $this->HandleFilters($allSkus);

        $responseContent = [
            'hydra:member' => new \ArrayIterator($data->getIterator()),
            'hydra:view' => [
                '@id' => $this->HandlePagination($request, $data->getCurrentPage()),
                'hydra:first' => $this->HandlePagination($request, 1),
                'hydra:last' => $this->HandlePagination($request, $data->getLastPage()),
                'hydra:previous' => $data->getCurrentPage() > 1
                    ? $this->HandlePagination($request, $data->getCurrentPage() - 1)
                    : null,
            ],
            'itemsPerPage' => $data->getItemsPerPage(),
            'hydra:filter' => $filter,
            'hydra:totalItems' => $data->getTotalItems(),
        ];

        return $this->json(
            $responseContent,
            Response::HTTP_OK,
            [],
            ['groups' => ['product:read','attribute:read']]
        );


    }

    private function HandlePagination(Request $request, $page)
    {
        if ($page < 1) {
            return null;
        }
        $url = $request->getRequestUri();
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'] ?? '', $queryParams);
        unset($queryParams['page']);
        $queryParams['page'] = $page;
        $newQueryString = http_build_query($queryParams);
        $newUrl = $parsedUrl['path'] . '?' . $newQueryString;
        return $newUrl;
    }


    private function HandleFilters(array $skus)
    {
        $data = $this->attributeMainRepository->findAttributesBySkus($skus);
        return $data;
    }

    private function ParseFilter(Request $request):array
    {
        $filters = [];
        $queryString = $request->server->get('QUERY_STRING'); // Get raw query string

        if ($queryString) {
            // Split the query string into individual parameters
            $pairs = explode('&', $queryString);

            foreach ($pairs as $pair) {
                $parts = explode('=', $pair, 2); // Split key and value
                if (count($parts) === 2) {
                    $key = urldecode($parts[0]);
                    $value = urldecode($parts[1]);

                    // Check if the key is part of "filter"
                    if (preg_match('/^filter\[(\d+)]$/', $key, $matches)) {
                        $filterKey = $matches[1]; // Extract the numeric key
                        if (!isset($filters[$filterKey])) {
                            $filters[$filterKey] = [];
                        }
                        $filters[$filterKey][] = $value; // Append the value
                    }
                }
            }
        }

        return $filters;
    }

    private function HandleSpecial($userExtId): array
    {
        $mataks = [];
        $res = $this->productRepository->getSpecialProducts();
        foreach ($res as $itemRec){
            $mataks[] = $itemRec->getSku();
        }
        return $mataks;
    }

    private function HandleNewProducts($userExtId): array
    {
        $mataks = [];
        $res =  $this->productRepository->getNewProducts();
        foreach ($res as $itemRec){
            $mataks[] = $itemRec->getSku();
        }
        return $mataks;
    }

    private function HandleProductsImBuy($userExtId): array
    {
        return $this->erpManager->ProductsImBuy($userExtId);
    }

    private function HandleProductsImNotBuy($userExtId): array
    {
        $skusImBut = $this->erpManager->ProductsImBuy($userExtId);

        $products = $this->productRepository->findAll();

        $filteredProducts = array_filter($products, function ($product) use ($skusImBut) {
            return !in_array($product->getSku(), $skusImBut);
        });

        $skusForSearch = array_map(function ($product) {
            return $product->getSku();
        }, $filteredProducts);
        return $skusForSearch;
    }

    private function HandleBonuses(Paginator $paginator, User $user)
    {
        foreach ($paginator->getIterator() as $item) {
            assert($item instanceof Product);
            $res = $this->bonusRepository->findBonusesByUserAndProduct($user->getExtId(), $item->getId());
            foreach ($res as $bonus) {
                assert($bonus instanceof Bonus);
                foreach ($bonus->getBonusDetaileds() as &$bonusDetailed) {
                    assert($bonusDetailed instanceof BonusDetailed);
                    $this->priceChecker->GetOnlinePriceForSku($user,$bonusDetailed->getBonusProduct());
                }
            }
            if(!empty($res)){
                $item->addBonus($res);
            }
        }
    }

}