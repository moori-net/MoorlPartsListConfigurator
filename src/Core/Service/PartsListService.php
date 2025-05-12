<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Service;

use Doctrine\DBAL\Connection;
use Moorl\PartsListConfigurator\Core\Calculator\PartsListCalculatorInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class PartsListService
{
    /**
     * @param PartsListCalculatorInterface[] $partsListCalculators
     */
    public function __construct(
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
        private readonly Connection $connection,
        private readonly SystemConfigService $systemConfigService,
        private readonly CartService $cartService,
        private readonly LoggerInterface $logger,
        private readonly iterable $partsListCalculators
    )
    {
    }

    public function getRepository(string $name): EntityRepository
    {
        return $this->definitionInstanceRegistry->getRepository($name);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function getPartsListCalculators(): array
    {
        $names = [];
        foreach ($this->partsListCalculators as $c) {
            $names[] = $c->getName();
        }
        return $names;
    }
}
