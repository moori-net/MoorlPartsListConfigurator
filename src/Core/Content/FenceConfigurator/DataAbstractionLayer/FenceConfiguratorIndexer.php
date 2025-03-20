<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\DataAbstractionLayer;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Doctrine\DBAL\Connection;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FenceConfiguratorIndexer extends EntityIndexer
{
    private readonly EntityRepository $repository;

    public function __construct(
        private readonly Connection $connection,
        private readonly IteratorFactory $iteratorFactory,
        EntityRepository $repository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        $this->repository = $repository;
    }

    public function getName(): string
    {
        return 'moorl_fc.indexer';
    }

    public function iterate(?array $offset): ?EntityIndexingMessage
    {
        $iterator = $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);

        $ids = $iterator->fetch();

        if (empty($ids)) {
            return null;
        }

        return new FenceConfiguratorIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $entityEvent = $event->getEventByEntityName(FenceConfiguratorDefinition::ENTITY_NAME);

        if (!$entityEvent) {
            return null;
        }

        $ids = $entityEvent->getIds();

        foreach ($entityEvent->getWriteResults() as $result) {
            if (!$result->getExistence()) {
                continue;
            }
        }

        if (empty($ids)) {
            return null;
        }

        return new FenceConfiguratorIndexingMessage(array_values($ids), null, $event->getContext(), \count($ids) > 20);
    }

    public function getTotal(): int
    {
        return $this->getIterator(null)->fetchCount();
    }

    public function getDecorated(): EntityIndexer
    {
        throw new DecorationPatternException(self::class);
    }

    public function handle(EntityIndexingMessage $message): void
    {
        $ids = $message->getData();

        $ids = array_unique(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $context = $message->getContext();

        $this->eventDispatcher->dispatch(new FenceConfiguratorIndexerEvent($ids, $context));
    }

    private function getIterator(?array $offset): IterableQuery
    {
        return $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);
    }
}
