<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Service;

use Moorl\PartsListConfigurator\Core\Calculator\PartsListCalculatorInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class PartsListService
{
    /**
     * @param PartsListCalculatorInterface[] $partsListCalculators
     */
    public function __construct(
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
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
        $config = [];
        foreach ($this->partsListCalculators as $c) {
            $config[$c->getName()] = $c->getMapping();
        }
        return $config;
    }
}
