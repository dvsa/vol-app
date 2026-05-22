<?php

return [
    'service_manager' => [
        'aliases' => [
            'Utils\NiTextTranslation' => \Dvsa\Olcs\Utils\Translation\NiTextTranslation::class,
            'Utils\MissingTranslationProcessor' => \Dvsa\Olcs\Utils\Translation\MissingTranslationProcessor::class,
        ],
        'factories' => [
            \Dvsa\Olcs\Utils\Translation\NiTextTranslation::class => \Dvsa\Olcs\Utils\Translation\NiTextTranslation::class,
            \Dvsa\Olcs\Utils\Translation\MissingTranslationProcessor::class =>
                \Dvsa\Olcs\Utils\Translation\MissingTranslationProcessorFactory::class,
            \Dvsa\Olcs\Utils\Client\HttpExternalClientFactory::class =>
                \Dvsa\Olcs\Utils\Client\HttpExternalClientFactory::class,
            'MvcTranslator' => \Laminas\I18n\Translator\TranslatorServiceFactory::class,
        ],
        'delegators' => [
            'MvcTranslator' => [
                \Dvsa\Olcs\Utils\Translation\TranslatorDelegatorFactory::class,
                \Dvsa\Olcs\Utils\Translation\MissingTranslationDelegatorFactory::class,
            ],
            \Laminas\I18n\Translator\Translator::class => [
                \Dvsa\Olcs\Utils\Translation\TranslatorDelegatorFactory::class,
                \Dvsa\Olcs\Utils\Translation\MissingTranslationDelegatorFactory::class,
            ],
        ],
        'shared' => [
            \Dvsa\Olcs\Utils\Client\HttpExternalClientFactory::class => false,
        ]
    ],
    'view_helpers' => [
        'factories' => [
            'getPlaceholder' => \Dvsa\Olcs\Utils\View\Factory\Helper\GetPlaceholderFactory::class,
            'assetPath' => \Dvsa\Olcs\Utils\View\Factory\Helper\AssetPathFactory::class,
        ]
    ]
];
