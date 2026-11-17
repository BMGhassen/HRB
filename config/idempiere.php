<?php

return [

    /*
    |--------------------------------------------------------------------------
    | iDempiere REST API Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for connecting to the iDempiere modern REST API (/api/v1).
    | These values are loaded from your .env file.
    |
    */

    'base_url'           => env('IDEMPIERE_URL'),

    // Session identifiers
    'client_id'          => (int) env('IDEMPIERE_CLIENT_ID', 11),
    'role_id'            => (int) env('IDEMPIERE_ROLE_ID', 102),
    'org_id'             => (int) env('IDEMPIERE_ORG_ID', 11),
    'warehouse_id'       => (int) env('IDEMPIERE_WAREHOUSE_ID', 114),
    'language'           => env('IDEMPIERE_LANGUAGE', 'en_US'),

    // API credentials — no defaults on purpose: never fall back to SuperUser/System
    'username'           => env('IDEMPIERE_USERNAME'),
    'password'           => env('IDEMPIERE_PASSWORD'),

    // Sync settings
    'price_list_version_id' => (int) env('IDEMPIERE_PRICE_LIST_VERSION_ID', 101),
    'sync_batch_size'    => (int) env('IDEMPIERE_SYNC_BATCH_SIZE', 100),
    'order_doc_type_id'  => (int) env('IDEMPIERE_ORDER_DOC_TYPE_ID', 1000034), // BL = Sales Order

    // How long (seconds) to cache the JWT token before refreshing (23 hours)
    'token_ttl'          => (int) env('IDEMPIERE_TOKEN_TTL', 82800),

    // Request timeout in seconds
    'timeout'            => (int) env('IDEMPIERE_TIMEOUT', 30),

];
