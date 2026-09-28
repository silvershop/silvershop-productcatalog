<?php

declare(strict_types=1);

namespace SilverShop\ProductCatalog\Extension;

use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\DataList;

/**
 * Lets a category be placed and filled with products without the site tree.
 *
 * Adds a parent selector (normally set by dragging pages around the tree)
 * and a "Products" tab listing the products whose main category this is.
 *
 * @extends Extension<ProductCategory>
 */
class ProductCategoryExtension extends Extension
{
    /**
     * Products whose main category or additional categories include this one.
     *
     * @return DataList<Product>
     */
    public function CatalogProducts(): DataList
    {
        $id = (int) $this->getOwner()->ID;

        return Product::get()->filterAny([
            'ParentID' => $id,
            'ProductCategories.ID' => $id,
        ]);
    }

    protected function updateCMSFields(FieldList $fields): void
    {
        $category = $this->getOwner();

        $fields->addFieldToTab(
            'Root.Main',
            TreeDropdownField::create(
                'ParentID',
                _t(__CLASS__ . '.Parent', 'Parent category or page'),
                SiteTree::class
            )
                ->setEmptyString(_t(__CLASS__ . '.TopLevel', 'Top level'))
                ->setHasEmptyDefault(true)
                // Products can't hold categories, and a category can't live beneath itself
                ->setFilterFunction(fn (SiteTree $page): bool => !$page instanceof Product)
                ->setDisableFunction(fn (SiteTree $page): bool => $category->ID && (int) $page->ID === (int) $category->ID),
            'Content'
        );

        if (!$category->isInDB()) {
            return;
        }

        $config = GridFieldConfig_RecordEditor::create();

        // New products added here start out in this category
        $config->getComponentByType(GridFieldDetailForm::class)?->setItemEditFormCallback(
            function (Form $form, GridFieldDetailForm_ItemRequest $itemRequest) use ($category): void {
                if ($itemRequest->getRecord()->isInDB()) {
                    return;
                }

                $form->Fields()->dataFieldByName('ParentID')?->setValue($category->ID);
            }
        );

        $fields->addFieldToTab(
            'Root.Products',
            GridField::create(
                'CatalogProducts',
                _t(__CLASS__ . '.Products', 'Products'),
                Product::get()->filter('ParentID', $category->ID),
                $config
            )->setDescription(_t(
                __CLASS__ . '.ProductsDescription',
                'Products whose main category is this one. Products listed here via "Additional Categories" are managed on the product.'
            ))
        );
    }
}
