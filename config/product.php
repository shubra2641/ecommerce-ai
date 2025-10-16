<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Product package settings
    |--------------------------------------------------------------------------
    |
    | This configuration file provides integration points for the product
    | features. Consumers can publish/copy this file into their projects and
    | adjust limits and defaults without editing code.
    |
    */

    // Pagination for product listing
    'per_page' => env('PRODUCTS_PER_PAGE', 10),

    // Maximum number of images allowed per product
    'max_images' => env('PRODUCT_MAX_IMAGES', 10),

    // Default stock value
    'default_stock' => env('PRODUCT_DEFAULT_STOCK', 0),
];
