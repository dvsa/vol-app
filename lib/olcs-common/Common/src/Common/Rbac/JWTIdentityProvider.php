<?php

declare(strict_types=1);

namespace Common\Rbac;

use Common\Auth\Service\RefreshTokenService;
use Common\Service\Cqrs\Query\QuerySender;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Exception;
use Laminas\Authentication\Storage\Session;
use Laminas\Http\Response;
use Laminas\Session\Container;
use LmcRbacMvc\Identity\IdentityInterface;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * @see JWTIdentityProviderFactory
 */
class JWTIdentityProvider implements IdentityProviderInterface
{
    private ?IdentityInterface $identity = null;

    public function __construct(private Container $identitySession, private QuerySender $querySender, private CacheEncryption $cacheService, private RefreshTokenService $refreshTokenService, private Session $tokenSession)
    {
    }

    #[\Override]
    public function getIdentity()
    {
        $this->refreshTokenIfRequired();

        if (!is_null($this->identity)) {
            return $this->identity;
        }

        $identity = $this->identitySession->offsetGet('identity');

        if (!$this->shouldUpdateIdentity($identity) && $this->cacheHasIdentity($identity->getId())) {
            $data = $this->fetchIdentityFromCache($identity->getId());
            $this->refreshTokenIfRequired();
        } else {
            $data = $this->fetchIdentityFromDB();
            $identity = new User();
            $identity->setId($data['id']);

            if (!empty($data['roles']) && is_array($data['roles'])) {
                $identity->setRoles(array_column($data['roles'], 'role'));
            }
        }

        $identity->setUserType($data['userType']);
        $identity->setUsername($data['loginId']);
        $identity->setUserData($data);

        $this->identity = $identity;

        $this->identitySession->offsetSet('identity', $this->identity);
        return $this->identity;
    }

    private function shouldUpdateIdentity(?User $identity): bool
    {
        if (!($identity instanceof User)) {
            // no identity in the session yet - refresh
            return true;
        }
        //no user id - refresh
        return empty($identity->getId());
    }

    /**
     * @return array|mixed
     */
    private function fetchIdentityFromDB()
    {
        $this->querySender->setRecoverHttpClientException(true);
        $response = $this->querySender->send(MyAccount::create([]));

        if (!$response->isOk()) {
            $response->setResult(
                [
                    'id' => null,
                    'userType' => User::USER_TYPE_NOT_IDENTIFIED,
                    'loginId' => null,
                    'roles' => []
                ]
            );
        }

        return $response->getResult();
    }

    private function fetchIdentityFromCache(int $userId): array
    {
        return $this->cacheService->getCustomItem(CacheEncryption::USER_ACCOUNT_IDENTIFIER, (string)$userId);
    }

    private function cacheHasIdentity(int $userId): bool
    {
        return $this->cacheService->hasCustomItem(CacheEncryption::USER_ACCOUNT_IDENTIFIER, (string)$userId);
    }

    private function refreshTokenIfRequired(): void
    {
        if ($this->tokenSession->isEmpty()) {
            return;
        }

        $tokens = $this->getToken();
        $identifier = $this->getIdentifierFromToken();
        if (is_null($tokens) || !$this->refreshTokenService->isRefreshRequired($tokens) || is_null($identifier)) {
            return;
        }

        try {
            $newTokens = $this->refreshTokenService->refreshTokens($tokens, $identifier);
            $this->tokenSession->write($newTokens);
        } catch (Exception) {
            return;
        }
    }

    /**
     * This method replicates the response we used to get from OpenAm
     * Initially, it appears only to be used by the Javascript ajax request when loading modal boxes
     */
    public function validateToken(): array
    {
        $response = [
            'status' => Response::STATUS_CODE_200,
            'valid' => false,
        ];

        //fetching the identity will also refresh the token if possible
        $identity = $this->getIdentity();

        //anon user or empty token, return valid = false
        if ($identity->isAnonymous() || $this->tokenSession->isEmpty()) {
            return $response;
        }

        $token = $this->getToken();

        //expired or missing token, return valid = false
        if (is_null($token) || $this->tokenExpired($token)) {
            return $response;
        }

        $tokenIdentifer = $this->getIdentifierFromToken();
        $username = $identity->getUsername();

        //if username from identity and token don't match, return valid = false
        if ($tokenIdentifer !== $username) {
            return $response;
        }

        $response['valid'] = true;
        $response['uid'] = $username;

        return $response;
    }

    public function clearSession(): void
    {
        $this->identitySession->exchangeArray([]);
        $this->tokenSession->clear();
        $this->identity = null;
    }

    private function getToken(): ?array
    {
        return $this->tokenSession->read()['Token'] ?? null;
    }

    private function getIdentifierFromToken(): ?string
    {
        return $this->tokenSession->read()['AccessTokenClaims']['username'] ?? null;
    }

    private function tokenExpired(array $token): bool
    {
        return $token['expires'] - time() < 0;
    }
}
