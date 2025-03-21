<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldMultiEntityCollection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldThingCollection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Content\Seo\SeoUrl\SeoUrlDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\NoConstraint;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FenceConfiguratorDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_fc';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return FenceConfiguratorCollection::class;
    }

    public function getEntityClass(): string
    {
        return FenceConfiguratorEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => false
        ];
    }

    protected function defineFields(): FieldCollection
    {
        $collection = [
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('fence_configurator_media_id', 'coverId', FenceConfiguratorMediaDefinition::class))->addFlags(new ApiAware(), new NoConstraint()),
            (new FkField('product_line_property_id', 'productLinePropertyId', PropertyGroupOptionDefinition::class))->addFlags(new Required()),
            (new FkField('fence_stream_id', 'fenceStreamId', ProductStreamDefinition::class))->addFlags(new Required()),
            (new FkField('fence_post_stream_id', 'fencePostStreamId', ProductStreamDefinition::class))->addFlags(new Required()),
            (new FkField('fence_other_stream_id', 'fenceOtherStreamId', ProductStreamDefinition::class))->addFlags(new Required()),
            new FkField('cms_page_id', 'cmsPageId', CmsPageDefinition::class),

            (new BoolField('active', 'active'))->addFlags(new EditField('switch')),
            (new TranslatedField('name'))->addFlags(new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING), new EditField('text')),
            (new TranslatedField('teaser'))->addFlags(new EditField('textarea')),
            (new TranslatedField('keywords'))->addFlags(new EditField('textarea')),
            (new TranslatedField('description'))->addFlags(new EditField('textarea')),
            (new TranslatedField('metaTitle'))->addFlags(new EditField('text')),
            (new TranslatedField('metaDescription'))->addFlags(new EditField('textarea')),
            (new TranslatedField('slotConfig'))->addFlags(),
            (new OneToManyAssociationField('seoUrls', SeoUrlDefinition::class, 'foreign_key'))->addFlags(new ApiAware()),

            (new TranslationsAssociationField(FenceConfiguratorTranslationDefinition::class, 'moorl_fc_id'))->addFlags(new Required()),

            (new ManyToOneAssociationField('cmsPage', 'cms_page_id', CmsPageDefinition::class))->addFlags(),
            (new ManyToManyAssociationField('options', PropertyGroupOptionDefinition::class, FenceConfiguratorOptionDefinition::class, 'moorl_fc_id', 'property_group_option_id'))->addFlags(new ApiAware(), new CascadeDelete()),
            (new ManyToManyAssociationField('postOptions', PropertyGroupOptionDefinition::class, FenceConfiguratorPostOptionDefinition::class, 'moorl_fc_id', 'property_group_option_id'))->addFlags(new ApiAware(), new CascadeDelete()),
            (new ManyToManyAssociationField('logicalOptions', PropertyGroupOptionDefinition::class, FenceConfiguratorLogicalOptionDefinition::class, 'moorl_fc_id', 'property_group_option_id'))->addFlags(new ApiAware(), new CascadeDelete()),
            (new ManyToManyAssociationField('logicalOptions', PropertyGroupOptionDefinition::class, FenceConfiguratorLogicalOptionDefinition::class, 'moorl_fc_id', 'property_group_option_id'))->addFlags(new ApiAware(), new CascadeDelete()),
            (new ManyToOneAssociationField('cover', 'fence_configurator_media_id', FenceConfiguratorMediaDefinition::class, 'id'))->addFlags(new ApiAware()),
            (new OneToManyAssociationField('media', FenceConfiguratorMediaDefinition::class, 'moorl_fc_id'))->addFlags(new ApiAware(), new CascadeDelete()),
        ];

        $fieldCollection = new FieldCollection(array_merge(
            $collection,
            FieldMultiEntityCollection::getFieldItems([])
        ));

        return $fieldCollection;
    }
}
