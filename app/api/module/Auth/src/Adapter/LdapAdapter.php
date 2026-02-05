<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\Adapter;

use Dvsa\Authentication\Ldap\Client;
use Dvsa\Contracts\Auth\AccessTokenInterface;
use Dvsa\Contracts\Auth\Exceptions\ChallengeException;
use Dvsa\Contracts\Auth\Exceptions\ClientException;
use Dvsa\Contracts\Auth\Exceptions\InvalidTokenException;
use Dvsa\Contracts\Auth\OAuthClientInterface;
use Dvsa\Contracts\Auth\ResourceOwnerInterface;
use Dvsa\Olcs\Auth\Exception\ResetPasswordException;
use Dvsa\Olcs\Transfer\Result\Auth\ChangeExpiredPasswordResult;
use Dvsa\Olcs\Transfer\Result\Auth\ChangePasswordResult;
use Dvsa\Olcs\Transfer\Result\Auth\DeleteUserResult;
use Laminas\Authentication\Adapter\AbstractAdapter;
use Laminas\Authentication\Result;
use Olcs\Logging\Log\Logger;

class LdapAdapter extends AbstractAdapter
{
    public function __construct(protected Client $client)
    {
    }

    public function authenticate(): Result
    {
        try {
            $token = $this->client->authenticate($this->getIdentity(), $this->getCredential());
            return new Result(Result::SUCCESS, $this->buildUserObject($token));
        } catch (InvalidTokenException | ClientException $e) {
            Logger::err(
                sprintf(
                    'There was an error attempting to login the user %s: %s',
                    $this->getIdentity(),
                    $e->getMessage()
                ),
                $e->getTrace()
            );
            return new Result(Result::FAILURE, [], [$e->getMessage()]);
        }
    }

    public function changePassword(string $identifier, string $previousPassword, string $newPassword): ChangePasswordResult
    {
        try {
            $this->client->authenticate($identifier, $previousPassword);
        } catch (ClientException $e) {
            Logger::debug('LDAP client: change password ClientException checking previous password: ' . $e->getMessage());
            return new ChangePasswordResult(ChangePasswordResult::FAILURE_CLIENT_ERROR, $e->getMessage());
        } catch (ChallengeException $e) {
            // Do nothing as this means the password was valid
        }

        if ($previousPassword === $newPassword) {
            return new ChangePasswordResult(ChangePasswordResult::FAILURE_PASSWORD_REUSE, ChangePasswordResult::MESSAGE_PASSWORD_REUSE);
        }

        try {
            $this->client->changePassword($identifier, $newPassword);
            return new ChangePasswordResult(ChangePasswordResult::SUCCESS, ChangePasswordResult::MESSAGE_GENERIC_SUCCESS);
        } catch (ClientException $e) {
            Logger::debug('Cognito client: change password ClientException: ' . $e->getMessage());

            return new ChangePasswordResult(ChangePasswordResult::FAILURE, ChangePasswordResult::MESSAGE_GENERIC_FAIL);
        }
    }

    /**
     * @throws ResetPasswordException
     */
    public function resetPassword(string $identifier, string $newPassword): bool
    {
        try {
            return $this->client->changePassword($identifier, $newPassword);
        } catch (ClientException $e) {
            Logger::debug('Ldap client: reset password ClientException: ' . $e->getMessage());
            throw new ResetPasswordException($e->getMessage());
        } catch (\Exception $e) {
            Logger::err('Unknown reset password error from Cognito client: ' . $e->getMessage());
            throw new ResetPasswordException($e->getMessage());
        }
    }

    /**
     * @throws ClientException
     */
    public function register(string $identifier, string $password, string $email, array $attributes = []): void
    {
        // The `sn` attribute is required while using the object class `inetOrgPerson`.
        // This is a local authentication adapter so the data in the `sn` field isn't important.
        $attributes['sn'] = $identifier;

        $this->client->register($identifier, $password, $attributes);
    }

    public function changeExpiredPassword(string $newPassword, string $challengeToken, string $username): ChangeExpiredPasswordResult
    {
        try {
            $token = $this->client->authenticate($username, $newPassword);
        } catch (ClientException $e) {
            Logger::err('Ldap client: change password ClientException checking previous password: ' . $e->getMessage());
            return new ChangeExpiredPasswordResult(ChangeExpiredPasswordResult::FAILURE_CLIENT_ERROR, [], [$e->getMessage()]);
        }

        return new ChangeExpiredPasswordResult(ChangeExpiredPasswordResult::SUCCESS, $this->buildUserObject($token));
    }

    public function refreshToken(string $refreshToken, string $identifier): Result
    {
        // No refresh token functionality in LDAP.
        return new Result(Result::FAILURE, []);
    }

    /**
     * @throws ClientException
     */
    public function changeAttribute(string $identifier, string $key, string $value): void
    {
        $this->client->changeAttribute($identifier, $key, $value);
    }

    public function deleteUser(string $identifier): DeleteUserResult
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * @throws ClientException
     */
    public function disableUser(string $identifier): void
    {
        $this->client->disableUser($identifier);
    }

    /**
     * @throws ClientException
     */
    public function enableUser(string $identifier): void
    {
        $this->client->enableUser($identifier);
    }

    /**
     * @throws ClientException
     */
    public function getUserByIdentifier(string $identifier): ResourceOwnerInterface
    {
        return $this->client->getUserByIdentifier($identifier);
    }

    /**
     * @throws ClientException
     */
    public function registerIfNotPresent(string $identifier, string $password, string $email, array $attributes = []): bool
    {
        if (!$this->doesUserExist($identifier)) {
            $this->register($identifier, $password, $email, $attributes);
            return true;
        }
        return false;
    }

    /**
     * @throws ClientException
     */
    public function doesUserExist(string $identifier): bool
    {
        try {
            $this->getUserByIdentifier($identifier);
        } catch (ClientException $e) {
            throw $e;
        }

        return true;
    }

    /**
     * @return mixed|string
     */
    #[\Override]
    public function getIdentity()
    {
        $identity = parent::getIdentity();
        if (!empty($identity) && is_string($identity)) {
            $identity = strtolower($identity);
        }
        return $identity;
    }

    /**
     * @throws InvalidTokenException
     */
    private function buildUserObject(AccessTokenInterface $token): array
    {
        $idTokenClaims = $this->client->decodeToken($token->getIdToken());
        $accessTokenClaims = $this->client->decodeToken($token->getToken());
        $resourceOwner = $this->client->getResourceOwner($token);

        return [
            'Provider' => OAuthClientInterface::class,
            'Token' => $token,
            'ResourceOwner' => $resourceOwner,
            'AccessToken' => $token->getToken(),
            'AccessTokenClaims' => $accessTokenClaims,
            'IdToken' => $token->getIdToken(),
            'IdTokenClaims' => $idTokenClaims,
            'RefreshToken' => $token->getRefreshToken(),
        ];
    }
}
