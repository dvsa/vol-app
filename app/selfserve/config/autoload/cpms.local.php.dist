<?php

return array(
    'cpms_api'                => array(
        'version'           => 1, //CPMS API version to use
        'logger_alias'      => 'Zend\Log', //Zend logger service manager alias
        'identity_provider' => 'Cpms\IdentityProvider', //Should implement CpmsClient\Authenticate\IdentityProviderInterface
        'enable_cache'      => true,
        'cache_storage'     => 'filesystem',
        'rest_client'   => array(
            'options' => array(
                'domain' => 'payment-service.psdv-ap01.ps.npm'
            ),
        ),
    ),

    'cpms_credentials' => array(
        'user_id' => '1234',
        'client_id' => 'OLCS',
        'client_secret' => 'f7c3cd7d55c0dc0f3bcadc68d2867beedc28b463'
    )
);
