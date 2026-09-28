<?php

declare(strict_types=1);

namespace SilverShop\ProductCatalog\Tests\Admin;

use SilverShop\Admin\ProductCatalogAdmin;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverStripe\Admin\CMSMenu;
use SilverStripe\Dev\FunctionalTest;

class ProductCatalogAdminTest extends FunctionalTest
{
    protected static $fixture_file = '../Fixtures/catalog.yml';

    private const PRODUCT_TAB = 'SilverShop-Page-Product';

    private const CATEGORY_TAB = 'SilverShop-Page-ProductCategory';

    protected function setUp(): void
    {
        parent::setUp();

        $this->logInWithPermission('ADMIN');
    }

    public function testManagesProductsAndCategories(): void
    {
        $models = ProductCatalogAdmin::singleton()->getManagedModels();

        $this->assertSame(Product::class, $models[Product::class]['dataClass']);
        $this->assertSame(ProductCategory::class, $models[ProductCategory::class]['dataClass']);
    }

    public function testListsProducts(): void
    {
        $response = $this->get('admin/catalog/' . self::PRODUCT_TAB);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Plain T-Shirt', $response->getBody());
        $this->assertStringContainsString('Sun Hat', $response->getBody());
    }

    public function testListsCategoriesWithTheirPath(): void
    {
        $response = $this->get('admin/catalog/' . self::CATEGORY_TAB);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Clothing &gt; Shirts', $response->getBody());
    }

    public function testEditsProductInAdmin(): void
    {
        $product = $this->objFromFixture(Product::class, 'tshirt');

        $response = $this->get(sprintf(
            'admin/catalog/%1$s/EditForm/field/%1$s/item/%2$d/edit',
            self::PRODUCT_TAB,
            $product->ID
        ));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Plain T-Shirt', $response->getBody());
    }

    public function testCmsEditLinksPointAtCatalogAdmin(): void
    {
        $product = $this->objFromFixture(Product::class, 'tshirt');
        $category = $this->objFromFixture(ProductCategory::class, 'clothing');

        $this->assertStringContainsString(
            sprintf('admin/catalog/%1$s/EditForm/field/%1$s/item/%2$d', self::PRODUCT_TAB, $product->ID),
            $product->getCMSEditLink()
        );
        $this->assertStringContainsString(
            sprintf('admin/catalog/%1$s/EditForm/field/%1$s/item/%2$d', self::CATEGORY_TAB, $category->ID),
            $category->getCMSEditLink()
        );
    }

    public function testAppearsInCmsMenu(): void
    {
        CMSMenu::populate_menu();
        $menuClasses = array_map(fn ($item) => $item->controller, CMSMenu::get_menu_items());

        $this->assertContains(ProductCatalogAdmin::class, $menuClasses);
    }
}
