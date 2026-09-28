<?php

declare(strict_types=1);

namespace SilverShop\ProductCatalog\Tests\Extension;

use Page;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverShop\ProductCatalog\Extension\HideCatalogFromSiteTreeExtension;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;

class HideCatalogFromSiteTreeExtensionTest extends FunctionalTest
{
    protected static $fixture_file = '../Fixtures/catalog.yml';

    public function testHiddenPageTypesIncludeSubclasses(): void
    {
        Config::modify()->set(HideCatalogFromSiteTreeExtension::class, 'hidden_page_types', [Page::class]);

        $hidden = HideCatalogFromSiteTreeExtension::getHiddenPageTypes();

        $this->assertContains(Page::class, $hidden);
        $this->assertContains(Product::class, $hidden);
        $this->assertContains(ProductCategory::class, $hidden);
    }

    public function testAppliesToSiteTreeConfig(): void
    {
        Config::modify()->set(SiteTree::class, 'hide_pagetypes', ['Some\\Other\\PageType']);

        HideCatalogFromSiteTreeExtension::applyToSiteTree();
        // Applying twice must not duplicate entries
        HideCatalogFromSiteTreeExtension::applyToSiteTree();

        $hidePageTypes = Config::inst()->get(SiteTree::class, 'hide_pagetypes', Config::UNINHERITED);

        $this->assertContains('Some\\Other\\PageType', $hidePageTypes);
        $this->assertContains(Product::class, $hidePageTypes);
        $this->assertContains(ProductCategory::class, $hidePageTypes);
        $this->assertSame($hidePageTypes, array_values(array_unique($hidePageTypes)));

        $hideFromTree = Config::inst()->get(SiteTree::class, 'hide_from_cms_tree');

        $this->assertContains(Product::class, $hideFromTree);
        $this->assertContains(ProductCategory::class, $hideFromTree);
    }

    public function testCatalogPagesAreRemovedFromCmsTree(): void
    {
        $this->logInWithPermission('ADMIN');

        $body = $this->get('admin/pages/treeview')->getBody();

        $this->assertStringContainsString('About Us', $body);
        $this->assertStringNotContainsString('Clothing', $body);
        $this->assertStringNotContainsString('Plain T-Shirt', $body);
    }

    public function testCatalogPagesRemainOnTheFrontEnd(): void
    {
        $category = $this->objFromFixture(ProductCategory::class, 'clothing');

        $this->assertCount(1, $category->stageChildren(true)->filter('ClassName', ProductCategory::class));
    }
}
