<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Administration\Controller;

use Moorl\PartsListConfigurator\Core\Service\PartsListService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/moorl-pl', defaults: ['_routeScope' => ['api']])]
class PartsListConfiguratorController
{
    public function __construct(protected PartsListService $partsListService)
    {
    }

    #[Route(path: '/get-parts-list-calculators', name: 'api.moorl-pl.get-parts-list-calculators', methods: ['GET'])]
    public function getPartsListCalculators(): JsonResponse
    {
        return new JsonResponse($this->partsListService->getPartsListCalculators());
    }
}
