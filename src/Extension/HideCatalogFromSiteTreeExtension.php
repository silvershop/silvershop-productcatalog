<?php

declare(strict_types=1);

namespace SilverShop\ProductCatalog\Extension;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;

/**
 * Removes the catalog page types from the CMS site tree.
 *
 * Silverstripe provides `SiteTree.hide_from_cms_tree` (tree and list views)
 * and `SiteTree.hide_pagetypes` (the "Add page" and "Change page type"
 * options), but both match class names exactly. Projects commonly subclass
 * `Product`, so the configured types are expanded to include their
 * subclasses before the CMS renders anything.
 *
 * @extends Extension<LeftAndMain>
 */
class HideCatalogFromSiteTreeExtension extends Extension
{
    use Configurable;

    /**
     * Page types (and their subclasses) to hide from the CMS site tree.
     */
    private static array $hidden_page_types = [];

    /**
     * The configured page types, expanded to include every subclass.
     *
     * @return string[]
     */
    public static function getHiddenPageTypes(): array
    {
        $classes = [];

        foreach (static::config()->get('hidden_page_types') ?? [] as $pageType) {
            if (!class_exists($pageType)) {
                continue;
            }

            // subclassesFor() is keyed by lowercase name and includes the class itself
            $classes = array_merge($classes, array_values(ClassInfo::subclassesFor($pageType)));
        }

        return array_values(array_unique($classes));
    }

    /**
     * Add the hidden page types to the site tree config for this request.
     */
    public static function applyToSiteTree(): void
    {
        $hidden = static::getHiddenPageTypes();

        if ($hidden === []) {
            return;
        }

        foreach (['hide_from_cms_tree', 'hide_pagetypes'] as $setting) {
            $existing = Config::inst()->get(SiteTree::class, $setting, Config::UNINHERITED) ?? [];

            Config::modify()->set(
                SiteTree::class,
                $setting,
                array_values(array_unique(array_merge($existing, $hidden)))
            );
        }
    }

    protected function onBeforeInit(): void
    {
        static::applyToSiteTree();
    }
}
