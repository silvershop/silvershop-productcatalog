<?php

declare(strict_types=1);

namespace SilverShop\ProductCatalog\Tests\Admin;

use SilverShop\Admin\ProductCatalogAdmin;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverShop\ProductCatalog\Admin\ShopAdmin;
use SilverStripe\Admin\CMSMenu;
use SilverStripe\Dev\FunctionalTest;

class ShopAdminTest extends FunctionalTest
{
    protected static $fixture_file = '../Fixtures/catalog.yml';

    protected function setUp(): void
    {
        parent::setUp();

        $this->logInWithPermission('ADMIN');
    }

    public function testManagesProductsAndCategories(): void
    {
        $models = ShopAdmin::singleton()->getManagedModels();

        $this->assertSame(Product::class, $models['products']['dataClass']);
        $this->assertSame(ProductCategory::class, $models['categories']['dataClass']);
    }

    public function testListsProducts(): void
    {
        $response = $this->get('admin/shop/products');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Plain T-Shirt', $response->getBody());
        $this->assertStringContainsString('Sun Hat', $response->getBody());
    }

    public function testListsCategoriesWithTheirPath(): void
    {
        $response = $this->get('admin/shop/categories');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Clothing &gt; Shirts', $response->getBody());
    }

    public function testEditsProductInAdmin(): void
    {
        $product = $this->objFromFixture(Product::class, 'tshirt');

        $response = $this->get('admin/shop/products/EditForm/field/products/item/' . $product->ID . '/edit');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Plain T-Shirt', $response->getBody());
    }

    public function testCmsEditLinksPointAtShopAdmin(): void
    {
        $product = $this->objFromFixture(Product::class, 'tshirt');
        $category = $this->objFromFixture(ProductCategory::class, 'clothing');

        $this->assertStringContainsString(
            'admin/shop/products/EditForm/field/products/item/' . $product->ID,
            $product->getCMSEditLink()
        );
        $this->assertStringContainsString(
            'admin/shop/categories/EditForm/field/categories/item/' . $category->ID,
            $category->getCMSEditLink()
        );
    }

    public function testReplacesCoreCatalogMenuItem(): void
    {
        CMSMenu::populate_menu();
        $menuClasses = array_map(fn ($item) => $item->controller, CMSMenu::get_menu_items());

        $this->assertContains(ShopAdmin::class, $menuClasses);
        $this->assertNotContains(ProductCatalogAdmin::class, $menuClasses);
    }
}
