<?php

use Aws\S3\S3Client;
use Dvsa\Olcs\Email\Domain\Command;
use Dvsa\Olcs\Email\Domain\CommandHandler;
use Dvsa\Olcs\Email\Service;

return [
    'email' => [
        'from_name'   => 'OLCS do not reply',
        'from_email'  => 'donotreply@otc.gsi.gov.uk',
        'selfserve_uri' => 'http://olcs-selfserve/',
        'internal_uri'  => 'http://olcs-internal/',
    ],

    'service_manager' => [
        'factories' => [
            // The Email class is its own factory (implements FactoryInterface)
            Service\Email::class => Service\Email::class,
            Service\TemplateRenderer::class => Service\TemplateRendererFactory::class,
            'ImapService' => Service\Imap::class,
            // New: make Aws\S3\S3Client resolvable from awsOptions config
            S3Client::class => function ($c) {
                $cfg = $c->get('config')['awsOptions'] ?? [];
                return new S3Client([
                    'region'                  => $cfg['region']  ?? 'eu-west-1',
                    'version'                 => $cfg['version'] ?? 'latest',
                    'use_path_style_endpoint' => $cfg['s3']['use_path_style_endpoint'] ?? false,
                ]);
            },
        ],
        'aliases' => [
            'translator'   => 'MvcTranslator',
            'EmailService' => Service\Email::class,
        ],
    ],

    'view_helpers' => [
        'invokables' => [
            'emailStyle' => \Dvsa\Olcs\Email\View\Helper\EmailStyle::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'layout' => __DIR__ . '/../view/layout',
            'email'  => __DIR__ . '/../view/email',
        ],
    ],

    \Dvsa\Olcs\Api\Domain\CommandHandlerManagerFactory::CONFIG_KEY => [
        'factories' => [
            Command\SendEmail::class => CommandHandler\SendEmail::class,
            Command\ProcessInspectionRequestEmail::class => CommandHandler\ProcessInspectionRequestEmail::class,
            Command\UpdateInspectionRequest::class => CommandHandler\UpdateInspectionRequest::class,
        ],
    ],
];
