<?php

declare(strict_types=1);

namespace SilverShop\ProductCatalog\Tests\Extension;

use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\TreeDropdownField;

class ProductCategoryExtensionTest extends SapphireTest
{
    protected static $fixture_file = '../Fixtures/catalog.yml';

    public function testCatalogProductsIncludesAdditionalCategories(): void
    {
        $shirts = $this->objFromFixture(ProductCategory::class, 'shirts');

        $this->assertListEquals(
            [['Title' => 'Plain T-Shirt'], ['Title' => 'Sun Hat']],
            $shirts->CatalogProducts()
        );
    }

    public function testAddsParentSelector(): void
    {
        $fields = ProductCategory::create()->getCMSFields();

        $this->assertInstanceOf(TreeDropdownField::class, $fields->dataFieldByName('ParentID'));
        $this->assertNull($fields->dataFieldByName('CatalogProducts'), 'Unsaved categories have no products tab');
    }

    public function testListsMainCategoryProducts(): void
    {
        $clothing = $this->objFromFixture(ProductCategory::class, 'clothing');
        $field = $clothing->getCMSFields()->dataFieldByName('CatalogProducts');

        $this->assertInstanceOf(GridField::class, $field);
        $this->assertListEquals([['Title' => 'Sun Hat']], $field->getList());
    }
}
