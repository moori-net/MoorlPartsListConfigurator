<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\Cms;

use MoorlFoundation\Core\Content\Cms\FoundationListingCmsElementResolver;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class FenceConfiguratorListingCmsElementResolver extends FoundationListingCmsElementResolver
{
    public function getType(): string
    {
        return 'look-listing';
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $data = new FenceConfiguratorListingStruct();
        $slot->setData($data);

        $request = $resolverContext->getRequest();
        $salesChannelContext = $resolverContext->getSalesChannelContext();

        $navigationId = $this->getNavigationId($resolverContext);

        $criteria = new Criteria();
        $criteria->addAssociation('cover');

        $this->enrichCmsElementResolverCriteriaV2($slot, $criteria, $resolverContext);

        /* Enrich additional filters */
        $config = $slot->getFieldConfig();
        $listingSourceConfig = $config->get('listingSource');
        if ($listingSourceConfig && $listingSourceConfig->getValue() === 'static') {
            $magazineCategoryIdConfig = $config->get('creatorId');
            if ($magazineCategoryIdConfig && $magazineCategoryIdConfig->getValue()) {
                $criteria->addFilter(new EqualsFilter(
                    'moorl_fc.creatorId',
                    $magazineCategoryIdConfig->getValue()
                ));
            }
        }

        $listing = $this->listingRoute
            ->load($navigationId, $request, $salesChannelContext, $criteria)
            ->getResult();

        $data->setListing($listing);
    }
}
