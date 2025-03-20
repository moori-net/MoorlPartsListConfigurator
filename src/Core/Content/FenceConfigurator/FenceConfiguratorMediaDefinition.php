<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\Aggregate\FenceConfiguratorMedia;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ReverseInherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FenceConfiguratorMediaDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_fc_media';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return FenceConfiguratorMediaCollection::class;
    }

    public function getEntityClass(): string
    {
        return FenceConfiguratorMediaEntity::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return FenceConfiguratorDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new VersionField())->addFlags(new ApiAware(), new Required()),
            (new FkField('moorl_fc_id', 'fenceConfiguratorId', FenceConfiguratorDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(FenceConfiguratorDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new IntField('position', 'position'))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('look', 'moorl_fc_id', FenceConfiguratorDefinition::class, 'id', false))->addFlags(),
            (new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id', true))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware()),
        ]);
    }
}
