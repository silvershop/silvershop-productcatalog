<?php

declare(strict_types=1);

namespace SilverShop\ProductCatalog\Admin;

use SilverShop\Admin\ProductBulkLoader;
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
 */
class ShopAdmin extends ModelAdmin
{
    private static string $url_segment = 'shop';

    private static string $menu_title = 'Shop';

    private static int $menu_priority = 5;

    private static string $menu_icon_class = 'font-icon-p-shop';

    private static array $managed_models = [
        'products' => [
            'dataClass' => Product::class,
            'title' => 'Products',
        ],
        'categories' => [
            'dataClass' => ProductCategory::class,
            'title' => 'Categories',
        ],
        'attributes' => [
            'dataClass' => AttributeType::class,
            'title' => 'Attributes',
        ],
    ];

    private static array $model_importers = [
        'products' => ProductBulkLoader::class,
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
