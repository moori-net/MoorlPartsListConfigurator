<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\Product;

use Moorl\FenceConfigurator\Core\Content\Look\Aggregate\LookProduct\LookProductDefinition;
use Moorl\FenceConfigurator\Core\Content\Look\LookDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'looks',
                LookDefinition::class,
                LookProductDefinition::class,
                'product_id',
                'moorl_fc_id'
            ))->addFlags(new Inherited())
        );
    }
}
