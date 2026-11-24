<?php

/**
 * Points the API at the local stand-ins for GOV.UK One Login and the DVSA Address Service. See
 * "Mock GOV.UK One Login and address lookup" in docs/app/local-setup.
 *
 * compose-mocks.yaml starts them and switches this on by setting VOL_LOCAL_MOCKS in the api and
 * cli containers. It is loaded after local.php, so it wins, and only in development.
 */

if (!filter_var(getenv('VOL_LOCAL_MOCKS'), FILTER_VALIDATE_BOOLEAN)) {
    return [];
}

$signIn = 'http://mocksignin.local.olcs.dev-dvsacloud.uk';
$address = 'http://mockaddress.local.olcs.dev-dvsacloud.uk';

return [
    // The client id and signing keys still come from local.php: the mock accepts any, but there
    // have to be some.
    'govuk_account' => [
        'discovery_endpoint' => $signIn . '/.well-known/openid-configuration',
        'core_identity_did_document_url' => $signIn . '/.well-known/did.json',
        'expected_core_identity_issuer' => $signIn,
    ],
    // Only used while the use_new_address_service feature toggle is on.
    'dvsa_address_service' => [
        'client' => [
            'base_uri' => $address,
        ],
        'oauth2' => [
            'client_id' => 'local',
            'client_secret' => 'local',
            'token_url' => $address . '/local/oauth2/v2.0/token',
            'scope' => 'api://local/.default',
        ],
    ],
];
