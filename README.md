# SilverShop Product Catalog

Manage [SilverShop](https://github.com/silvershop/silvershop-core) products and
categories from a dedicated **Catalog** admin instead of the CMS site tree.

Stores with hundreds or thousands of products quickly bury the rest of the site
in the pages section. This module keeps products and categories as pages (so
URLs, versioning, publishing and preview all work as before), but removes them
from the site tree and edits them through a `ModelAdmin` instead.

## Requirements

- PHP 8.3+
- Silverstripe CMS 6
- SilverShop Core 6.2+ (earlier versions ship their own `ProductCatalogAdmin`)

## Installation

```sh
composer require silvershop/productcatalog
```

Then flush your cache (`vendor/bin/sake flush`).

## What it does

- Adds a **Catalog** section to the CMS (`admin/catalog`) with tabs for
  Products, Categories and Attributes, including the product CSV importer from
  core. This is the `SilverShop\Admin\ProductCatalogAdmin` that previously
  shipped with silvershop/core, so modules that add to its `managed_models` or
  extend it (e.g. silvershop/stock, silvershop/comparison,
  silvershop/coloredvariations) work unchanged.
- Hides `Product` and `ProductCategory` pages (and any subclasses) from the
  CMS site tree, and from the "Add page" and "Change page type" options.
- Adds a **Parent category or page** field to categories so they can be placed
  in the hierarchy without dragging them around the tree, and a **Products** tab
  listing the products whose main category it is. Products added from that tab
  start out in the category.
- Points CMS edit links for products and categories (reports, link pickers,
  etc.) at the Catalog admin.

Products and categories still appear on the front end exactly as before;
only the CMS tree is affected.

## Configuration

To keep using the site tree as well as the Catalog admin, clear the list
entirely:

```yml
---
Name: app-productcatalog
After: '#silvershop-productcatalog'
---
SilverShop\ProductCatalog\Extension\HideCatalogFromSiteTreeExtension:
  hidden_page_types: null
```

Hide additional page types from the site tree (subclasses are included
automatically):

```yml
SilverShop\ProductCatalog\Extension\HideCatalogFromSiteTreeExtension:
  hidden_page_types:
    - App\Page\Brand
```

Keep categories in the site tree and only move products into the Catalog admin
(config lists merge, so clear the list before setting it):

```yml
---
Name: app-productcatalog-reset
After: '#silvershop-productcatalog'
---
SilverShop\ProductCatalog\Extension\HideCatalogFromSiteTreeExtension:
  hidden_page_types: null
---
Name: app-productcatalog
After: '#app-productcatalog-reset'
---
SilverShop\ProductCatalog\Extension\HideCatalogFromSiteTreeExtension:
  hidden_page_types:
    - SilverShop\Page\Product
```

## Caveats

- Silverstripe's `hide_from_cms_tree` only applies to the tree and list views.
  Searching the pages section can still return products and categories.
- Non-catalog pages placed beneath a hidden category are hidden with it.

## Running the tests

```sh
composer install
vendor/bin/phpunit
```

## License

BSD-2-Clause. See [LICENSE](LICENSE).
