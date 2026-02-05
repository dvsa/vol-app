<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\Adapter;

use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Aws\Exception\AwsException;
use Dvsa\Authentication\Cognito\Client;
use Dvsa\Contracts\Auth\AccessTokenInterface;
use Dvsa\Contracts\Auth\Exceptions\ChallengeException;
use Dvsa\Contracts\Auth\Exceptions\ClientException;
use Dvsa\Contracts\Auth\OAuthClientInterface;
use Dvsa\Contracts\Auth\Exceptions\InvalidTokenException;
use Dvsa\Contracts\Auth\ResourceOwnerInterface;
use Dvsa\Olcs\Auth\Exception\ResetPasswordException;
use Dvsa\Olcs\Transfer\Result\Auth\ChangeExpiredPasswordResult;
use Dvsa\Olcs\Transfer\Result\Auth\ChangePasswordResult;
use Dvsa\Olcs\Transfer\Result\Auth\DeleteUserResult;
use Laminas\Authentication\Adapter\AbstractAdapter;
use Laminas\Authentication\Result;
use Olcs\Logging\Log\Logger;

class CognitoAdapter extends AbstractAdapter
{
    /**
     * Authentication success.
     */
    public const SUCCESS_WITH_CHALLENGE = 2;
    public const FAILURE_ACCOUNT_DISABLED = -5;

    public const MESSAGE_INCORRECT_USERNAME_OR_PASSWORD = 'Incorrect username or password.';
    public const MESSAGE_USER_IS_DISABLED = 'User is disabled.';
    public const MESSAGE_USER_DELETE_FAILED = 'Cognito client: failed to delete user %s: %s';

    public const EXCEPTION_INVALID_PASSWORD = 'InvalidPasswordException';
    public const EXCEPTION_NOT_AUTHORIZED = 'NotAuthorizedException';
    public const EXCEPTION_USER_NOT_FOUND = 'UserNotFoundException';

    /**
     * CognitoAdapter constructor.
     */
    public function __construct(protected Client $client)
    {
    }

    /**
     * @return Result
     */
    public function authenticate(): Result
    {
        try {
            $token = $this->client->authenticate($this->getIdentity(), $this->getCredential());
            return new Result(Result::SUCCESS, $this->buildUserObject($token));
        } catch (ChallengeException $e) {
            return new Result(
                static::SUCCESS_WITH_CHALLENGE,
                [],
                [
                    'challengeName' => $e->getChallengeName(),
                    'challengeParameters' => $e->getParameters(),
                    'challengeSession' => $e->getSession()
                ]
            );
        } catch (InvalidTokenException | ClientException $e) {
            $previous = $e->getPrevious();
            if ($previous instanceof CognitoIdentityProviderException) {
                switch ($previous->getAwsErrorCode()) {
                    case static::EXCEPTION_USER_NOT_FOUND:
                        return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, [], [$e->getMessage()]);
                    case static::EXCEPTION_NOT_AUTHORIZED && $e->getMessage() === static::MESSAGE_INCORRECT_USERNAME_OR_PASSWORD:
                        return new Result(Result::FAILURE_CREDENTIAL_INVALID, [], [$e->getMessage()]);
                    case static::EXCEPTION_NOT_AUTHORIZED && $e->getMessage() === static::MESSAGE_USER_IS_DISABLED:
                        return new Result(static::FAILURE_ACCOUNT_DISABLED, [], [$e->getMessage()]);
                }
            }
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

    public function changePassword(string $identifier, string $previousPassword, string $newPassword, bool $permanent = true): ChangePasswordResult
    {
        try {
            $this->client->authenticate($identifier, $previousPassword);
        } catch (ClientException $e) {
            $previousException = $e->getPrevious();
            $errorCode = $previousException->getAwsErrorCode();
            $errorMessage = $previousException->getAwsErrorMessage();
            assert($previousException instanceof AwsException);
            if ($errorCode === static::EXCEPTION_NOT_AUTHORIZED && $errorMessage === static::MESSAGE_INCORRECT_USERNAME_OR_PASSWORD) {
                return new ChangePasswordResult(ChangePasswordResult::FAILURE_OLD_PASSWORD_INVALID, ChangePasswordResult::MESSAGE_OLD_PASSWORD_INVALID);
            }
            Logger::debug('Cognito client: change password ClientException checking previous password: ' . $e->getMessage());
            return new ChangePasswordResult(ChangePasswordResult::FAILURE_CLIENT_ERROR, $e->getMessage());
        } catch (ChallengeException $e) {
            // Do nothing as this means the password was valid
        }

        if ($previousPassword === $newPassword) {
            return new ChangePasswordResult(ChangePasswordResult::FAILURE_PASSWORD_REUSE, ChangePasswordResult::MESSAGE_PASSWORD_REUSE);
        }

        try {
            $this->client->changePassword($identifier, $newPassword, $permanent);
            return new ChangePasswordResult(ChangePasswordResult::SUCCESS, ChangePasswordResult::MESSAGE_GENERIC_SUCCESS);
        } catch (ClientException $e) {
            Logger::debug('Cognito client: change password ClientException: ' . $e->getMessage());
            $previousException = $e->getPrevious();
            assert($previousException instanceof AwsException);
            return match ($previousException->getAwsErrorCode()) {
                'InvalidPasswordException' => new ChangePasswordResult(ChangePasswordResult::FAILURE_NEW_PASSWORD_INVALID, ChangePasswordResult::MESSAGE_NEW_PASSWORD_INVALID),
                'NotAuthorizedException' => new ChangePasswordResult(ChangePasswordResult::FAILURE_NOT_AUTHORIZED, ChangePasswordResult::MESSAGE_GENERIC_FAIL),
                default => new ChangePasswordResult(ChangePasswordResult::FAILURE, ChangePasswordResult::MESSAGE_GENERIC_FAIL),
            };
        }
    }

    /**
     * @throws ResetPasswordException
     */
    public function resetPassword(string $identifier, string $newPassword, bool $permanent = true): bool
    {
        try {
            return $this->client->changePassword($identifier, $newPassword, $permanent);
        } catch (ClientException $e) {
            Logger::debug('Cognito client: reset password ClientException: ' . $e->getMessage());
            throw new ResetPasswordException($e->getMessage());
        } catch (\Exception $e) {
            Logger::err('Unknown reset password error from Cognito client: ' . $e->getMessage());
            throw new ResetPasswordException($e->getMessage());
        }
    }

    /**
     * @param array|null $attributes
     * @throws ClientException
     */
    public function register(string $identifier, string $password, string $email, array $attributes = []): void
    {
        $attributes = array_merge(['email' => $email], $attributes);
        $this->client->register($identifier, $password, $attributes);
    }

    /**
     * @return Result
     */
    public function changeExpiredPassword(string $newPassword, string $challengeToken, string $username): ChangeExpiredPasswordResult
    {
        try {
            $this->client->authenticate($username, $newPassword);
        } catch (ClientException $e) {
            $previousException = $e->getPrevious();
            assert($previousException instanceof AwsException);
            if ($previousException->getAwsErrorCode() === static::EXCEPTION_NOT_AUTHORIZED && $previousException->getAwsErrorMessage() === static::MESSAGE_INCORRECT_USERNAME_OR_PASSWORD) {
                // If authentication fails, we're good. It means the user is not using their existing password for
                // their new password. Break free :)
                goto break_free_of_authenticate;
            }
            Logger::err('Cognito client: change password ClientException checking previous password: ' . $e->getMessage());
            return new ChangeExpiredPasswordResult(ChangeExpiredPasswordResult::FAILURE_CLIENT_ERROR, [], [$e->getMessage()]);
        } catch (ChallengeException $e) {
            if ($e->getChallengeName() === 'NEW_PASSWORD_REQUIRED') {
                return new ChangeExpiredPasswordResult(ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_MATCHES_OLD, [], [$e->getMessage()]);
            }
        }

        break_free_of_authenticate:

        try {
            $token = $this->client->responseToAuthChallenge(
                'NEW_PASSWORD_REQUIRED',
                [
                    'NEW_PASSWORD' => $newPassword,
                    'USERNAME' => $username
                ],
                $challengeToken
            );

            return new ChangeExpiredPasswordResult(ChangeExpiredPasswordResult::SUCCESS, $this->buildUserObject($token));
        } catch (ChallengeException $e) {
            return new ChangeExpiredPasswordResult(
                ChangeExpiredPasswordResult::SUCCESS_WITH_CHALLENGE,
                [],
                [],
                [
                    'challengeName' => $e->getChallengeName(),
                    'challengeParameters' => $e->getParameters(),
                    'challengeSession' => $e->getSession()
                ]
            );
        } catch (ClientException $e) {
            return match ($e->getPrevious()->getAwsErrorCode()) {
                'InvalidPasswordException' => new ChangeExpiredPasswordResult(ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_INVALID, [], [$e->getMessage()]),
                'NotAuthorizedException' => new ChangeExpiredPasswordResult(ChangeExpiredPasswordResult::FAILURE_NOT_AUTHORIZED, [], [$e->getMessage()]),
                default => new ChangeExpiredPasswordResult(ChangeExpiredPasswordResult::FAILURE, [], [$e->getMessage()]),
            };
        } catch (InvalidTokenException $e) {
            return new ChangeExpiredPasswordResult(ChangeExpiredPasswordResult::FAILURE, [], [$e->getMessage()]);
        }
    }

    /**
     * @return Result
     */
    public function refreshToken(string $refreshToken, string $identifier): Result
    {
        try {
            $token = $this->client->refreshTokens($refreshToken, $identifier);
            return new Result(Result::SUCCESS, $this->buildUserObject($token));
        } catch (ChallengeException $e) {
            return new Result(
                static::SUCCESS_WITH_CHALLENGE,
                [],
                [
                    'challengeName' => $e->getChallengeName(),
                    'challengeParameters' => $e->getParameters(),
                    'challengeSession' => $e->getSession()
                ]
            );
        } catch (InvalidTokenException | ClientException $e) {
            return new Result(Result::FAILURE, [], [$e->getMessage()]);
        }
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
        try {
            $this->client->deleteUser($identifier);
            $code = DeleteUserResult::SUCCESS;
            $message = DeleteUserResult::MESSAGE_SUCCESS;
        } catch (ClientException $e) {
            $previousException = $e->getPrevious();
            assert($previousException instanceof AwsException);
            $previous = $previousException->getAwsErrorCode();

            switch ($previous) {
                case 'UserNotFoundException':
                    $code = DeleteUserResult::FAILURE_USER_NOT_FOUND;
                    $message = DeleteUserResult::MESSAGE_FAILURE_NOT_FOUND;
                    break;
                default:
                    Logger::err(sprintf(self::MESSAGE_USER_DELETE_FAILED, $identifier, $e->getMessage()));
                    $code = DeleteUserResult::FAILURE;
                    $message = DeleteUserResult::MESSAGE_FAILURE;
            }
        }

        return new DeleteUserResult($code, $message);
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
            if ($e->getPrevious()->getAwsErrorCode() === static::EXCEPTION_USER_NOT_FOUND) {
                return false;
            }
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
     * @return array
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
