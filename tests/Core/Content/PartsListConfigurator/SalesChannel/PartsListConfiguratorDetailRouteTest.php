<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Tests\Core\Content\PartsListConfigurator\SalesChannel;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorDefinition;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\PartsListConfiguratorDetailRoute;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\Exception\PageNotFoundException;
use Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsPageLoaderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class PartsListConfiguratorDetailRouteTest extends TestCase
{
    public function testMissingConfiguratorProducesNotFoundResponse(): void
    {
        $searchResult = $this->createStub(EntitySearchResult::class);
        $searchResult->method('first')->willReturn(null);

        $repository = $this->createStub(SalesChannelRepository::class);
        $repository->method('search')->willReturn($searchResult);

        $route = new PartsListConfiguratorDetailRoute(
            $repository,
            $this->createStub(SalesChannelCmsPageLoaderInterface::class),
            new PartsListConfiguratorDefinition()
        );
        $context = (new \ReflectionClass(SalesChannelContext::class))->newInstanceWithoutConstructor();

        $this->expectException(PageNotFoundException::class);

        $route->load(
            '01990bc7c9bb78f8bad6c81080922e49',
            new Request(),
            $context,
            new Criteria()
        );
    }
}
