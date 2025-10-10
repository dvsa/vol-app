<?php

use Dvsa\Olcs\Api\Domain\CommandHandlerManagerFactory;
use Dvsa\Olcs\Email\Domain\Command;
use Dvsa\Olcs\Email\Domain\CommandHandler;
use Dvsa\Olcs\Email\Service;
use Dvsa\Olcs\Email\View\Helper\EmailStyle;

return [
    'email' => [
        'from_name'     => 'OLCS do not reply',
        'from_email'    => 'donotreply@otc.gsi.gov.uk',
        'selfserve_uri' => 'http://olcs-selfserve/',
        'internal_uri'  => 'http://olcs-internal/',
    ],

    'service_manager' => [
        'factories' => [
            Service\Email::class            => Service\Email::class,
            Service\TemplateRenderer::class => Service\TemplateRendererFactory::class,
            'ImapService'                   => Service\Imap::class,
        ],
        'aliases'   => [
            'translator'   => 'MvcTranslator',
            'EmailService' => Service\Email::class,
        ],
    ],

    'view_helpers' => [
        'invokables' => [
            'emailStyle' => EmailStyle::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'layout' => __DIR__ . '/../view/layout',
            'email'  => __DIR__ . '/../view/email',
        ],
    ],

    CommandHandlerManagerFactory::CONFIG_KEY => [
        'factories' => [
            Command\SendEmail::class                     => CommandHandler\SendEmail::class,
            Command\ProcessInspectionRequestEmail::class => CommandHandler\ProcessInspectionRequestEmail::class,
            Command\UpdateInspectionRequest::class       => CommandHandler\UpdateInspectionRequest::class,
        ],
    ],
];
