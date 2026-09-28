<?php

declare(strict_types=1);

namespace SilverShop\Admin;

use SilverShop\Model\Variation\AttributeType;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;

/**
 * Manages products and product categories outside of the CMS site tree.
 *
 * Both types are still pages - they keep their URLs, versioning and preview -
 * but they are edited here rather than through the pages section, where they
 * are hidden by {@link \SilverShop\ProductCatalog\Extension\HideCatalogFromSiteTreeExtension}.
 *
 * This class previously shipped with silvershop/core and keeps its original
 * name so that existing configuration (e.g. other modules adding to
 * `managed_models`) continues to apply. Tabs are keyed by class name for the
 * same reason.
 */
class ProductCatalogAdmin extends ModelAdmin
{
    private static string $url_segment = 'catalog';

    private static string $menu_title = 'Catalog';

    private static int $menu_priority = 5;

    private static string $menu_icon_class = 'silvershop-icon-catalog';

    private static array $managed_models = [
        Product::class => [
            'title' => 'Products',
        ],
        ProductCategory::class => [
            'title' => 'Categories',
        ],
        AttributeType::class => [
            'title' => 'Attributes',
        ],
    ];

    private static array $model_importers = [
        Product::class => ProductBulkLoader::class,
    ];

    /**
     * Columns shown for the category list. Categories are nested, so the full
     * path is shown rather than just the title.
     */
    private static array $category_summary_fields = [
        'NestedTitle' => 'Title',
        'URLSegment' => 'URL segment',
        'CatalogProducts.Count' => 'Products',
    ];

    protected function getGridFieldConfig(): GridFieldConfig
    {
        $config = parent::getGridFieldConfig();

        if (is_a($this->modelClass, ProductCategory::class, true)) {
            $columns = $config->getComponentByType(GridFieldDataColumns::class);
            $columns?->setDisplayFields($this->config()->get('category_summary_fields'));
        }

        return $config;
    }
}
