<?php

use Knuckles\Scribe\Config\AuthIn;
use Knuckles\Scribe\Config\Defaults;
use Knuckles\Scribe\Extracting\Strategies;

use function Knuckles\Scribe\Config\configureStrategy;

return [
    'title' => 'Sincro API',
    'description' => 'RESTful API for the Sincro collaborative financial management application.',

    'intro_text' => <<<'INTRO'
    Welcome to the Sincro API documentation.

    This documentation provides an interactive guide to all available endpoints.
    Most endpoints require authentication. To interact with them:
    1.  Use the `POST /api/v1/auth/login` endpoint to get an `access_token`.
    2.  Click the "Authorize" button at the top of the page.
    3.  In the field, paste your token in the format: `Bearer <your_access_token>`.
    INTRO,

    'base_url' => env('APP_URL'),

    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/v1/*'],
                'domains' => ['*'],
            ],
            'exclude' => [],
        ],
    ],

    'type' => 'laravel',
    'theme' => 'elements',

    'static' => [
        'output_path' => 'public/docs',
    ],

    'laravel' => [
        'add_routes' => true,
        'docs_url' => '/docs',
        'assets_directory' => null,
        'middleware' => [],
    ],

    'try_it_out' => [
        'enabled' => true,
        'base_url' => null,
        'use_csrf' => false,
        'csrf_url' => '/sanctum/csrf-cookie',
    ],

    'auth' => [
        'enabled' => true,
        'default' => true,
        'in' => AuthIn::HEADER->value,
        'name' => 'Authorization',
        'placeholder' => 'Bearer {TOKEN}',

        'use_value' => env('SCRIBE_AUTH_TOKEN') ? 'Bearer '.env('SCRIBE_AUTH_TOKEN') : null,

        'extra_info' => 'You must be authenticated to access this endpoint. Use the access token obtained from the login endpoint.',
    ],

    'example_languages' => [
        'bash',
        'javascript',
    ],

    'postman' => [
        'enabled' => true,
        'overrides' => [],
    ],

    'openapi' => [
        'enabled' => true,
        'overrides' => [],
        'generators' => [],
    ],

    'groups' => [
        'default' => 'General Endpoints',
        'order' => [
            'Authentication',
            'User Profile',
            'Group Management',
            'Member Management',
            'Transactions',
        ],
    ],

    'logo' => 'img/sincro-logo.png',

    'last_updated' => 'Last updated: {date:F j, Y}',

    'examples' => [
        'faker_seed' => 1234,
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    'strategies' => [
        'metadata' => [
            ...Defaults::METADATA_STRATEGIES,
        ],
        'headers' => [
            ...Defaults::HEADERS_STRATEGIES,
            Strategies\StaticData::withSettings(data: [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]),
        ],
        'urlParameters' => [
            ...Defaults::URL_PARAMETERS_STRATEGIES,
        ],
        'queryParameters' => [
            ...Defaults::QUERY_PARAMETERS_STRATEGIES,
        ],
        'bodyParameters' => [
            ...Defaults::BODY_PARAMETERS_STRATEGIES,
        ],
        'responses' => configureStrategy(
            Defaults::RESPONSES_STRATEGIES,
            Strategies\Responses\ResponseCalls::withSettings(
                config: [
                    'app.debug' => false,
                ]
            )
        ),
        'responseFields' => [
            ...Defaults::RESPONSE_FIELDS_STRATEGIES,
        ],
    ],

    'database_connections_to_transact' => [config('database.default')],

    'fractal' => [
        'serializer' => null,
    ],
];
