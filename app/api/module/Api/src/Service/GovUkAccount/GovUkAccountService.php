<?php

namespace Dvsa\Olcs\Api\Service\GovUkAccount;

use Dvsa\GovUkAccount\Exception\InvalidTokenException;
use Dvsa\GovUkAccount\Provider\GovUkAccount;
use Dvsa\GovUkAccount\Provider\GovUkAccountUser;
use Dvsa\GovUkAccount\Token\AccessToken;
use Dvsa\Olcs\Api\Service\GovUkAccount\Response\GetAuthorisationUrlResponse;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class GovUkAccountService
{
    public const VOT_P0 = 'P0';
    public const VOT_P1 = 'P1';
    public const VOT_P2 = 'P2';
    public const JWT_TIMESTAMP_LEEWAY_SECONDS = 30;
    public const ERR_MISSING_NAMES = 'No name data available to process';

    public function __construct(protected array $config, protected GovUkAccount $provider)
    {
        JWT::$leeway = static::JWT_TIMESTAMP_LEEWAY_SECONDS;
    }

    public function getAuthorisationUrl(string $state, bool $identityAssurance = false, array $scopes = [], string $nonce = null): GetAuthorisationUrlResponse
    {
        if (empty($scopes)) {
            $scopes = $this->provider::DEFAULT_SCOPES;
        }

        $this->provider->setState($state);
        $this->provider->setNonce($nonce);

        $authorizationUrlParams = [
            'scope' => $scopes,
            'state' => $this->provider->getState(),
            'redirect_uri' => $this->config['redirect_uri']['logged_in'],
        ];

        if ($identityAssurance) {
            $authorizationUrlParams['vtr'] = '["P2.Cl.Cm"]';
            $authorizationUrlParams['claims'] = json_encode([
                "userinfo" => [
                    GovUkAccountUser::KEY_CLAIMS_CORE_IDENTITY => null,
                ]
            ]);
        }

        return new GetAuthorisationUrlResponse(
            $this->provider->getAuthorizationUrl($authorizationUrlParams),
            $this->provider->getState(),
            $this->provider->getNonce()
        );
    }

    /**
     * Creates a JWT with claims from $data and signs it. For use with the $state param on getAuthorisationUrl() which
     * is replayed when user returns the service.
     *
     * The method will set or overwrite the following values within the payload:
     *  - jti       A random ID prefixed with "guka_state_"
     *  - iat       Set to current unix timestamp
     *  - nbf       Set to current unix timestamp
     *  - exp       Set to current unix timestamp + $expireSeconds
     *
     * @param array $data The claims for the payload
     * @param int $expireSeconds The number of seconds the token will expire
     * @return string
     * @throws \Exception
     */
    public function createStateToken(array $data, int $expireSeconds = 2419200): string
    {
        $currentTimestamp = time();
        $data = array_merge($data, [
            'jti' => 'guka_state_' . bin2hex(random_bytes(16)),
            'iat' => $currentTimestamp,
            'nbf' => $currentTimestamp,
            'exp' => $currentTimestamp + $expireSeconds,
        ]);

        return JWT::encode($data, base64_decode((string) $this->config['keys']['private_key']), $this->config['keys']['algorithm']);
    }

    /**
     * Parses and validates the state JWT and returns the claims if successful.
     *
     * @return array
     */
    public function getStateClaimsFromToken(string $token): array
    {
        return (array) JWT::decode($token, new Key(base64_decode((string) $this->config['keys']['public_key']), $this->config['keys']['algorithm']));
    }

    /**
     * @throws IdentityProviderException
     */
    public function getAccessToken(string $code, array $scopes = [], string $grant = 'authorization_code'): AccessToken
    {
        if (empty($scopes)) {
            $scopes = $this->provider::DEFAULT_SCOPES;
        }

        return $this->provider->getAccessToken($grant, [
            'scope' => implode($this->provider::SCOPE_SEPARATOR, $scopes),
            'code' => $code
        ]);
    }

    public function getUserDetails(AccessToken $token): \League\OAuth2\Client\Provider\ResourceOwnerInterface
    {
        return $this->provider->getResourceOwner($token);
    }

    /**
     * @throws InvalidTokenException
     */
    public function verifyIdToken(AccessToken $token, string $nonce = null)
    {
        $this->provider->validateIdToken($token->getIdToken(), $nonce);
    }

    /**
     * Checks to see if the $actual vector of trust meets the $minimumConfidence required.
     *
     * Supports ONLY the vectors of trust supported by GOV.UK Account service.
     *
     * @see https://docs.sign-in.service.gov.uk/integrate-with-integration-environment/choose-the-level-of-identity-confidence/
     * @see https://datatracker.ietf.org/doc/html/rfc8485#appendix-A.1
     * @return bool
     */
    public static function meetsVectorOfTrust(string $actual, string $minimumConfidence): bool
    {
        $actual = strtoupper($actual);
        $minimumConfidence = strtoupper($minimumConfidence);
        $vectorsOfTrust = [self::VOT_P0, self::VOT_P1, self::VOT_P2];

        if (!in_array($minimumConfidence, $vectorsOfTrust)) {
            throw new \InvalidArgumentException("The minimumConfidence specified is not a valid value; supported values are: " . implode(',', $vectorsOfTrust));
        }

        if (!in_array($actual, $vectorsOfTrust)) {
            return false;
        }

        $actualIndex = array_search($actual, $vectorsOfTrust);
        $minimumConfidenceIndex = array_search($minimumConfidence, $vectorsOfTrust);

        if ($actualIndex >= $minimumConfidenceIndex) {
            return true;
        }

        return false;
    }

    /**
     * @see https://docs.sign-in.service.gov.uk/integrate-with-integration-environment/process-identity-information/#check-your-user-s-identity-credential-matches-the-level-of-confidence-needed
     *
     * A list showing the names proven by GOV.UK Sign In. This list reflects name changes by using the validFrom and
     * validUntil metadata properties. If validUntil is null or not present, that name is your user’s current name.
     * If validFrom is null or not present, your user may have used that name from birth.
     */
    public static function processNames(array $names): array
    {
        if (empty($names)) {
            throw new \InvalidArgumentException(self::ERR_MISSING_NAMES);
        }

        if (count($names) !== 1) {
            foreach ($names as $name) {
                if (!isset($name['validUntil'])) {
                    return self::extractNameData($name);
                }
            }
        }

        //we shouldn't get here, but will try to be as forgiving as possible if we do
        return self::extractNameData($names[0]);
    }

    /**
     * @see https://docs.sign-in.service.gov.uk/integrate-with-integration-environment/process-identity-information/#check-your-user-s-identity-credential-matches-the-level-of-confidence-needed
     *
     * Each name is presented as an array in the nameParts property.
     * Each part of the name is either a GivenName or a FamilyName, identified in its type property.
     * The value property could be any text string.
     */
    private static function extractNameData(array $name): array
    {
        $givenNames = [];
        $familyName = '';

        foreach ($name['nameParts'] as $part) {
            if ($part['type'] === 'GivenName') {
                $givenNames[] = $part['value'];
                continue;
            }

            $familyName = $part['value'];
        }

        return[
            'firstName' => implode(' ', $givenNames),
            'familyName' => $familyName,
        ];
    }
}
