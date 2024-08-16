<?php

return [
    'service_manager' => [
        'aliases' => [
            \Dvsa\Contracts\Auth\OAuthClientInterface::class => \Dvsa\Authentication\Cognito\Client::class,
        ],
        'factories' => [
            \Dvsa\Olcs\Auth\Service\AuthenticationServiceInterface::class => \Dvsa\Olcs\Auth\Service\AuthenticationServiceFactory::class,
            \Laminas\Authentication\Adapter\ValidatableAdapterInterface::class => \Dvsa\Olcs\Auth\Adapter\ValidatableAdapterFactory::class,
            \Dvsa\Authentication\Cognito\Client::class => \Dvsa\Olcs\Auth\Client\CognitoClientFactory::class,
            \Dvsa\Olcs\Auth\Adapter\CognitoAdapter::class => \Dvsa\Olcs\Auth\Adapter\CognitoAdapterFactory::class,
            \Dvsa\Olcs\Auth\Service\PasswordService::class => \Dvsa\Olcs\Auth\Service\PasswordServiceFactory::class,
            \Dvsa\Authentication\Ldap\Client::class => \Dvsa\Olcs\Auth\Client\LdapClientFactory::class,
            \Dvsa\Olcs\Auth\Adapter\LdapAdapter::class => \Dvsa\Olcs\Auth\Adapter\LdapAdapterFactory::class,
        ],
    ],
];
