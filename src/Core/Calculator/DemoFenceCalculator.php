<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use MoorlFoundation\Core\Content\ProductBuyList\ProductBuyListItemCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class DemoFenceCalculator implements CalculatorInterface
{
    public function getName(): string
    {
        return 'demo-fence';
    }

    public function getExpectedPropertyGroups(): array
    {
        return [
            'COLOR',
            'PARTS_LIST_LAYOUT',
            'HEIGHT',
            'LENGTH',
            'PARTS_LIST_POST_TYPE',
            'PARTS_LIST_POST_MOUNTING',
            'PARTS_LIST_POST_FLOOR_MOUNTING',
        ];
    }

    public function getOptionConfig(): array
    {
        return [
            [
                'technicalName' => 'COLOR',
                'required' => true
            ],
            [
                'technicalName' => 'HEIGHT',
                'required' => true
            ],
            [
                'technicalName' => 'PARTS_LIST_POST_TYPE',
                'hidden' => true
            ],
            [
                'technicalName' => 'PARTS_LIST_LAYOUT',
                'options' => [
                    [
                        'technicalName' => 'PARTS_LIST_LAYOUT_1',
                        'name' => 'length',
                        'elements' => [
                            ['name' => 'side_a', 'type' => 'number']
                        ]
                    ],
                    [
                        'technicalName' => 'PARTS_LIST_LAYOUT_2',
                        'name' => 'length',
                        'elements' => [
                            ['name' => 'side_a', 'type' => 'number'],
                            ['name' => 'side_b', 'type' => 'number']
                        ]
                    ],
                    [
                        'technicalName' => 'PARTS_LIST_LAYOUT_3',
                        'name' => 'length',
                        'elements' => [
                            ['name' => 'side_a', 'type' => 'number'],
                            ['name' => 'side_b', 'type' => 'number'],
                            ['name' => 'side_c', 'type' => 'number'],
                        ]
                    ],
                    [
                        'technicalName' => 'PARTS_LIST_LAYOUT_4',
                        'name' => 'length',
                        'elements' => [
                            ['name' => 'side_a', 'type' => 'number'],
                            ['name' => 'side_b', 'type' => 'number'],
                            ['name' => 'side_c', 'type' => 'number'],
                            ['name' => 'side_d', 'type' => 'number'],
                        ]
                    ]
                ]
            ]
        ];
    }

    public function getExpectedPropertyGroupOptions(): array
    {
        return [
            'PARTS_LIST_POST_TYPE_SIDE',
            'PARTS_LIST_POST_TYPE_CORNER',
        ];
    }

    public function calculate(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator
    ): ProductBuyListItemCollection
    {
        $productBuyList = new ProductBuyListItemCollection();

        return $productBuyList;
    }
}
