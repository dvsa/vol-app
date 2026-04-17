<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Adapter;

use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Aws\Exception\AwsException;
use Dvsa\Authentication\Cognito\Client;
use Dvsa\Contracts\Auth\AbstractResourceOwner;
use Dvsa\Contracts\Auth\AccessTokenInterface;
use Dvsa\Contracts\Auth\Exceptions\ChallengeException;
use Dvsa\Contracts\Auth\Exceptions\ClientException;
use Dvsa\Contracts\Auth\Exceptions\InvalidTokenException;
use Dvsa\Contracts\Auth\ResourceOwnerInterface;
use Dvsa\Olcs\Auth\Adapter\CognitoAdapter;
use Dvsa\Olcs\Transfer\Result\Auth\ChangeExpiredPasswordResult;
use Dvsa\Olcs\Transfer\Result\Auth\ChangePasswordResult;
use Dvsa\Olcs\Transfer\Result\Auth\DeleteUserResult;
use Laminas\Authentication\Result;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class CognitoAdapterTest
 * @see CognitoAdapter
 */
class CognitoAdapterTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function authenticateReturnsSuccessResultWhenDetailsAreCorrect(): void
    {
        // Setup
        $mockToken = m::mock(\Dvsa\Contracts\Auth\AccessTokenInterface::class);
        $mockToken->shouldReceive('getIdToken')->andReturn('idToken');
        $mockToken->shouldReceive('getToken')->andReturn('accessToken');
        $mockToken->shouldReceive('getRefreshToken')->andReturn('refreshToken');

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('authenticate')->andReturn($mockToken);
        $mockClient->shouldReceive('decodeToken')->andReturn([]);
        $mockClient->shouldReceive('getResourceOwner')
            ->andReturn(m::mock(ResourceOwnerInterface::class));

        $sut = new CognitoAdapter($mockClient);
        $sut->setIdentity('identity');
        $sut->setCredential('credential');

        // Execute
        $result = $sut->authenticate();

        // Assert
        static::assertEquals(Result::SUCCESS, $result->getCode());
        static::assertArrayHasKey('Token', $result->getIdentity());
        static::assertArrayHasKey('AccessToken', $result->getIdentity());
        static::assertArrayHasKey('IdToken', $result->getIdentity());
        static::assertArrayHasKey('RefreshToken', $result->getIdentity());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function authenticateReturnsFailureResultWhenInvalidTokenExceptionIsThrown(): void
    {
        // Setup
        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('authenticate')->andThrow(InvalidTokenException::class);

        $sut = new CognitoAdapter($mockClient);
        $sut->setIdentity('identity');
        $sut->setCredential('credential');

        // Execute
        $result = $sut->authenticate();

        // Assert
        static::assertEquals(Result::FAILURE, $result->getCode());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('authenticateCognitoIdentityProviderExceptionDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function authenticateReturnsExpectedFailureResultWhenCognitoIdentityProviderExceptionIsThrown(
        string $awsErrorCode,
        string $awsErrorMessage,
        int $expectedResultCode
    ): void {
        // Setup
        $previousException = m::mock(CognitoIdentityProviderException::class);
        $previousException->expects('getAwsErrorCode')->andReturn($awsErrorCode);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('authenticate')->andThrow(new ClientException($awsErrorMessage, 0, $previousException));

        $sut = new CognitoAdapter($mockClient);
        $sut->setIdentity('identity');
        $sut->setCredential('credential');

        // Execute
        $result = $sut->authenticate();

        // Assert
        static::assertEquals($expectedResultCode, $result->getCode());
    }

    public static function authenticateCognitoIdentityProviderExceptionDataProvider(): array
    {
        return [
            'User not found' => [
                CognitoAdapter::EXCEPTION_USER_NOT_FOUND,
                'message',
                Result::FAILURE_IDENTITY_NOT_FOUND
            ],
            'Incorrect credentials' => [
                CognitoAdapter::EXCEPTION_NOT_AUTHORIZED,
                CognitoAdapter::MESSAGE_INCORRECT_USERNAME_OR_PASSWORD,
                Result::FAILURE_CREDENTIAL_INVALID
            ],
            'User disabled' => [
                CognitoAdapter::EXCEPTION_NOT_AUTHORIZED,
                CognitoAdapter::MESSAGE_USER_IS_DISABLED,
                CognitoAdapter::FAILURE_ACCOUNT_DISABLED
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function authenticateReturnsChallengeResultWhenChallengeExceptionIsThrown(): void
    {
        // Setup
        $exception = new ChallengeException();
        $exception->setChallengeName('challengeName');
        $exception->setParameters([]);
        $exception->setSession('session');

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('authenticate')->andThrow($exception);

        $sut = new CognitoAdapter($mockClient);
        $sut->setIdentity('identity');
        $sut->setCredential('credential');

        // Execute
        $result = $sut->authenticate();

        // Assert
        static::assertEquals(CognitoAdapter::SUCCESS_WITH_CHALLENGE, $result->getCode());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function changedExpiredPasswordReturnsSuccessResultWhenDetailsAreCorrect(): void
    {
        // Setup
        $mockToken = m::mock(\Dvsa\Contracts\Auth\AccessTokenInterface::class);
        $mockToken->shouldReceive('getIdToken')->andReturn('idToken');
        $mockToken->shouldReceive('getToken')->andReturn('accessToken');
        $mockToken->shouldReceive('getRefreshToken')->andReturn('refreshToken');

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('responseToAuthChallenge')->andReturn($mockToken);
        $mockClient->shouldReceive('decodeToken')->andReturn([]);
        $mockClient->shouldReceive('getResourceOwner')
            ->andReturn(m::mock(ResourceOwnerInterface::class));

        $previousException = m::mock(AwsException::class);
        $previousException->expects('getAwsErrorCode')->andReturn(CognitoAdapter::EXCEPTION_NOT_AUTHORIZED);
        $previousException->expects('getAwsErrorMessage')->andReturn(CognitoAdapter::MESSAGE_INCORRECT_USERNAME_OR_PASSWORD);
        $mockClient->shouldReceive('authenticate')->andThrow(new ClientException('', 0, $previousException));

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->changeExpiredPassword('newPassword', 'challengeSession', 'username');

        // Assert
        static::assertEquals(Result::SUCCESS, $result->getCode());
        static::assertArrayHasKey('Token', $result->getIdentity());
        static::assertArrayHasKey('AccessToken', $result->getIdentity());
        static::assertArrayHasKey('IdToken', $result->getIdentity());
        static::assertArrayHasKey('RefreshToken', $result->getIdentity());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function changedExpiredPasswordReturnsExpectedFailureResultWhenInvalidTokenExceptionIsThrown(): void
    {
        // Setup
        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('responseToAuthChallenge')
            ->andThrow(InvalidTokenException::class);

        $previousException = m::mock(AwsException::class);
        $previousException->expects('getAwsErrorCode')->andReturn(CognitoAdapter::EXCEPTION_NOT_AUTHORIZED);
        $previousException->expects('getAwsErrorMessage')->andReturn(CognitoAdapter::MESSAGE_INCORRECT_USERNAME_OR_PASSWORD);
        $mockClient->shouldReceive('authenticate')->andThrow(new ClientException('', 0, $previousException));

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->changeExpiredPassword('newPassword', 'challengeSession', 'username');

        // Assert
        static::assertEquals(ChangeExpiredPasswordResult::FAILURE, $result->getCode());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function changedExpiredPasswordReturnsChallengeResultWhenChallengeExceptionIsThrown(): void
    {
        // Setup
        $exception = new ChallengeException();
        $exception->setChallengeName('challengeName');
        $exception->setParameters([]);
        $exception->setSession('session');

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('responseToAuthChallenge')->andThrow($exception);

        $previousException = m::mock(AwsException::class);
        $previousException->expects('getAwsErrorCode')->andReturn(CognitoAdapter::EXCEPTION_NOT_AUTHORIZED);
        $previousException->expects('getAwsErrorMessage')->andReturn(CognitoAdapter::MESSAGE_INCORRECT_USERNAME_OR_PASSWORD);
        $mockClient->shouldReceive('authenticate')->andThrow(new ClientException('', 0, $previousException));

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->changeExpiredPassword('newPassword', 'challengeSession', 'username');

        // Assert
        static::assertEquals(CognitoAdapter::SUCCESS_WITH_CHALLENGE, $result->getCode());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('changedExpiredPasswordClientExceptionDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function changedExpiredPasswordReturnsExpectedResultWhenClientExceptionIsThrown(string $awsErrorCode, int $expectedResultCode): void
    {
        // Setup
        $previousException = m::mock(AwsException::class);
        $previousException->expects('getAwsErrorCode')->andReturn($awsErrorCode);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('responseToAuthChallenge')
            ->andThrow(new ClientException('null', 0, $previousException));

        $previousException = m::mock(AwsException::class);
        $previousException->expects('getAwsErrorCode')->andReturn(CognitoAdapter::EXCEPTION_NOT_AUTHORIZED);
        $previousException->expects('getAwsErrorMessage')->andReturn(CognitoAdapter::MESSAGE_INCORRECT_USERNAME_OR_PASSWORD);
        $mockClient->shouldReceive('authenticate')->andThrow(new ClientException('', 0, $previousException));

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->changeExpiredPassword('newPassword', 'challengeSession', 'username');

        // Assert
        static::assertEquals($expectedResultCode, $result->getCode());
    }

    public static function changedExpiredPasswordClientExceptionDataProvider(): array
    {
        return [
            'Invalid Password' => [CognitoAdapter::EXCEPTION_INVALID_PASSWORD, ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_INVALID],
            'Unauthorised' => [CognitoAdapter::EXCEPTION_NOT_AUTHORIZED, ChangeExpiredPasswordResult::FAILURE_NOT_AUTHORIZED],
            'Generic' => ['generic', ChangeExpiredPasswordResult::FAILURE]
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function changedExpiredPasswordReturnsFailureNewPasswordIsExistingResultWhenNewPasswordIsSameAsExisting(): void
    {
        // Setup
        $exception = new ChallengeException();
        $exception->setChallengeName('NEW_PASSWORD_REQUIRED');
        $exception->setParameters([]);
        $exception->setSession('session');

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('authenticate')->andThrow($exception);

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->changeExpiredPassword('newPassword', 'challengeSession', 'username');

        // Assert
        static::assertInstanceOf(ChangeExpiredPasswordResult::class, $result);
        static::assertEquals(ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_MATCHES_OLD, $result->getCode());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function changedExpiredPasswordReturnsGeneralFailureResultWhenAuthenticateThrowsClientError(): void
    {
        // Setup
        $exception = new ClientException('', 0, m::mock(AwsException::class)->shouldIgnoreMissing());

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('authenticate')->andThrow($exception);

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->changeExpiredPassword('newPassword', 'challengeSession', 'username');

        // Assert
        static::assertInstanceOf(ChangeExpiredPasswordResult::class, $result);
        static::assertEquals(ChangeExpiredPasswordResult::FAILURE_CLIENT_ERROR, $result->getCode());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('changedPasswordClientExceptionDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function changePasswordReturnsExpectedResultWhenClientExceptionIsThrownOnPasswordChange(string $awsErrorCode, int $expectedResultCode): void
    {
        // Setup
        $previousException = m::mock(AwsException::class);
        $previousException->expects('getAwsErrorCode')->andReturn($awsErrorCode);

        $identifier = 'identifier';
        $previousPassword = 'previous password';
        $newPassword = 'new password';

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('authenticate');
        $mockClient->shouldReceive('changePassword')
            ->andThrow(new ClientException('null', 0, $previousException));

        $sut = new CognitoAdapter($mockClient);

        $result = $sut->changePassword($identifier, $previousPassword, $newPassword);

        // Assert
        static::assertEquals($expectedResultCode, $result->getCode());
    }

    public static function changedPasswordClientExceptionDataProvider(): array
    {
        return [
            'Invalid Password' => [CognitoAdapter::EXCEPTION_INVALID_PASSWORD, ChangePasswordResult::FAILURE_NEW_PASSWORD_INVALID],
            'Unauthorised' => [CognitoAdapter::EXCEPTION_NOT_AUTHORIZED, ChangePasswordResult::FAILURE_NOT_AUTHORIZED],
            'Generic' => ['generic', ChangePasswordResult::FAILURE]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('changedPasswordAuthClientExceptionDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function changePasswordReturnsExpectedResultWhenClientExceptionIsThrownOnAuth(string $awsErrorCode, string $awsErrorMessage, int $expectedResultCode): void
    {
        // Setup
        $previousException = m::mock(AwsException::class);
        $previousException->expects('getAwsErrorCode')->andReturn($awsErrorCode);
        $previousException->expects('getAwsErrorMessage')->andReturn($awsErrorMessage);

        $identifier = 'identifier';
        $previousPassword = 'previous password';
        $newPassword = 'new password';

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('authenticate')
            ->andThrow(new ClientException('null', 0, $previousException));
        $mockClient->shouldNotReceive('changePassword');

        $sut = new CognitoAdapter($mockClient);

        $result = $sut->changePassword($identifier, $previousPassword, $newPassword);

        // Assert
        static::assertEquals($expectedResultCode, $result->getCode());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function changePasswordReturnsExpectedResultWhenPasswordIsReused(): void
    {
        $identifier = 'identifier';
        $previousPassword = 'previous password';
        $newPassword = 'previous password';

        $mockClient = m::mock(Client::class);
        $mockClient->expects('authenticate');

        $sut = new CognitoAdapter($mockClient);

        $result = $sut->changePassword($identifier, $previousPassword, $newPassword);

        // Assert
        static::assertEquals(ChangePasswordResult::FAILURE_PASSWORD_REUSE, $result->getCode());
    }

    public static function changedPasswordAuthClientExceptionDataProvider(): array
    {
        return [
            'Invalid previous Password' => [
                CognitoAdapter::EXCEPTION_NOT_AUTHORIZED,
                CognitoAdapter::MESSAGE_INCORRECT_USERNAME_OR_PASSWORD,
                ChangePasswordResult::FAILURE_OLD_PASSWORD_INVALID
            ],
            'Client error' => [
                CognitoAdapter::EXCEPTION_NOT_AUTHORIZED,
                'Generic error',
                ChangePasswordResult::FAILURE_CLIENT_ERROR
            ],
            'Generic' => [
                'generic',
                'Generic error',
                ChangePasswordResult::FAILURE_CLIENT_ERROR
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function changePasswordReturnsSuccessResultWhenNoExceptions(): void
    {
        $identifier = 'identifier';
        $previousPassword = 'previous password';
        $newPassword = 'new password';

        $mockClient = m::mock(Client::class);
        $mockClient->expects('authenticate');
        $mockClient->expects('changePassword')
            ->with($identifier, $newPassword, true)
            ->andReturnTrue();

        $sut = new CognitoAdapter($mockClient);

        $result = $sut->changePassword($identifier, $previousPassword, $newPassword);

        // Assert
        static::assertEquals(ChangePasswordResult::SUCCESS, $result->getCode());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function changePasswordReturnsSuccessResultWhenAuthThrowsChallengeException(): void
    {
        // Setup
        $identifier = 'identifier';
        $previousPassword = 'previous password';
        $newPassword = 'new password';

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('authenticate')
            ->andThrow(new ChallengeException('null', 0));
        $mockClient->expects('changePassword')
            ->with($identifier, $newPassword, true)
            ->andReturnTrue();

        $sut = new CognitoAdapter($mockClient);

        $result = $sut->changePassword($identifier, $previousPassword, $newPassword);

        // Assert
        static::assertEquals(ChangePasswordResult::SUCCESS, $result->getCode());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registerWithException(): void
    {
        $this->expectException(ClientException::class);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('register')
            ->andThrow(ClientException::class);

        $sut = new CognitoAdapter($mockClient);
        $sut->register('identifier', 'password', 'email');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function refreshTokenReturnsSuccessResultWhenRefreshSucceeds(): void
    {
        // Setup
        $mockToken = m::mock(\Dvsa\Contracts\Auth\AccessTokenInterface::class);
        $mockToken->shouldReceive('getIdToken')->andReturn('newIdToken');
        $mockToken->shouldReceive('getToken')->andReturn('newAccessToken');
        $mockToken->shouldReceive('getRefreshToken')->andReturn('newRefreshToken');

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('refreshTokens')
            ->with('refreshToken', 'username')
            ->andReturn($mockToken);
        $mockClient->shouldReceive('decodeToken')->andReturn([]);
        $mockClient->shouldReceive('getResourceOwner')
            ->andReturn(m::mock(ResourceOwnerInterface::class));

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->refreshToken('refreshToken', 'username');
        $identity = $result->getIdentity();

        // Assert
        static::assertEquals(Result::SUCCESS, $result->getCode());
        static::assertArrayHasKey('Token', $identity);
        static::assertInstanceOf(AccessTokenInterface::class, $identity['Token']);
        static::assertArrayHasKey('AccessToken', $identity);
        static::assertEquals('newAccessToken', $identity['AccessToken']);
        static::assertArrayHasKey('IdToken', $identity);
        static::assertEquals('newIdToken', $identity['IdToken']);
        static::assertArrayHasKey('RefreshToken', $identity);
        static::assertEquals('newRefreshToken', $identity['RefreshToken']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function refreshTokenReturnsChallengeResultWhenChallengeExceptionIsThrown(): void
    {
        // Setup
        $exception = new ChallengeException();
        $exception->setChallengeName('challengeName');
        $exception->setParameters([]);
        $exception->setSession('session');

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('refreshTokens')->andThrow($exception);

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->refreshToken('refreshToken', 'username');

        // Assert
        static::assertEquals(CognitoAdapter::SUCCESS_WITH_CHALLENGE, $result->getCode());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('refreshTokenExceptionDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function refreshTokenReturnsFailureResultWhenOtherExceptionsAreThrown(string $exception): void
    {
        // Setup
        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('refreshTokens')->andThrow($exception);

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->refreshToken('refreshToken', 'username');

        // Assert
        static::assertEquals(Result::FAILURE, $result->getCode());
    }

    public static function refreshTokenExceptionDataProvider(): array
    {
        return [
            [InvalidTokenException::class],
            [ClientException::class]
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getUserByIdentifierReturnsUser(): void
    {
        // Setup
        $id = '1001';
        $username = 'user4574';

        $mockUser = m::mock(ResourceOwnerInterface::class);
        $mockUser->shouldReceive('getId')->andReturn($id);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('getUserByIdentifier')
            ->with($username)
            ->once()
            ->andReturn($mockUser);

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->getUserByIdentifier($username);

        // Assert
        static::assertInstanceOf(ResourceOwnerInterface::class, $result);
        static::assertEquals($id, $result->getId());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registerIfNotPresentReturnsFalseAndDoesNotRegistersUserWhenUserExists(): void
    {
        $id = '1001';
        $username = 'user4574';
        $password = 'P@s5w0rD!';
        $email = 'test@test.localdomain';

        // Setup
        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('getUserByIdentifier')->with($username)->once()->andReturn(
            new class (['id' => $id]) extends AbstractResourceOwner implements ResourceOwnerInterface {
                public function getId(): string
                {
                    return $this->get('id');
                }
            }
        );

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->registerIfNotPresent($username, $password, $email);

        // Assert
        static::assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registerIfNotPresentReturnsTrueAndRegistersUserWhenUserNotExists(): void
    {
        $username = 'user4574';
        $password = 'P@s5w0rD!';
        $email = 'test@test.localdomain';
        $attributes = [];

        // Setup
        $previousException = m::mock(AwsException::class);
        $previousException->expects('getAwsErrorCode')->andReturn(CognitoAdapter::EXCEPTION_USER_NOT_FOUND);
        $exception = new ClientException('null', 0, $previousException);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('getUserByIdentifier')->with($username)->once()->andThrow($exception);

        $compiledAttributes = array_merge(['email' => $email], $attributes);
        $mockClient->shouldReceive('register')->with($username, $password, $compiledAttributes)->once();

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->registerIfNotPresent($username, $password, $email, $attributes);

        // Assert
        static::assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registerIfNotPresentBubblesUnexpectedExceptionWhenClientThrowsUnexpectedException(): void
    {
        $username = 'user4574';
        $password = 'P@s5w0rD!';
        $email = 'test@test.localdomain';
        $attributes = [];

        // Setup
        $previousException = m::mock(AwsException::class);
        $previousException->expects('getAwsErrorCode')->andReturn('SomeOtherThing');
        $exception = new ClientException('null', 0, $previousException);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('getUserByIdentifier')->with($username)->once()->andThrow($exception);

        $sut = new CognitoAdapter($mockClient);

        $this->expectException(ClientException::class);

        // Execute
        $sut->registerIfNotPresent($username, $password, $email, $attributes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doesUserExistReturnsTrueWhenUserExistsInCognito(): void
    {
        // Setup
        $username = 'user4574';

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('getUserByIdentifier')->with($username)->once()->andReturn(
            new class (['id' => 1001]) extends AbstractResourceOwner implements ResourceOwnerInterface {
                public function getId(): string
                {
                    return $this->get('id');
                }
            }
        );

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->doesUserExist($username);

        // Assert
        static::assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doesUserExistReturnsFalseWhenUserDoesNotExistInCognito(): void
    {
        $username = 'user4574';

        // Setup
        $previousException = m::mock(AwsException::class);
        $previousException->expects('getAwsErrorCode')->andReturn(CognitoAdapter::EXCEPTION_USER_NOT_FOUND);
        $exception = new ClientException('null', 0, $previousException);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('getUserByIdentifier')->with($username)->once()->andThrow($exception);

        $sut = new CognitoAdapter($mockClient);

        // Execute
        $result = $sut->doesUserExist($username);

        // Assert
        static::assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doesUserExistBubblesUnexpectedExceptionWhenClientThrowsUnexpectedException(): void
    {
        $username = 'user4574';

        // Setup
        $previousException = m::mock(AwsException::class);
        $previousException->expects('getAwsErrorCode')->andReturn('SomeOtherThing');
        $exception = new ClientException('null', 0, $previousException);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('getUserByIdentifier')->with($username)->once()->andThrow($exception);

        $sut = new CognitoAdapter($mockClient);

        $this->expectException(ClientException::class);

        // Execute
        $sut->doesUserExist($username);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function deleteUserSuccess(): void
    {
        $identifier = 'user4574';

        $mockClient = m::mock(Client::class);
        $mockClient->expects('deleteUser')->with($identifier);

        $sut = new CognitoAdapter($mockClient);

        $deleteUserResult = $sut->deleteUser($identifier);

        $this->assertInstanceOf(DeleteUserResult::class, $deleteUserResult);
        $this->assertEquals(DeleteUserResult::SUCCESS, $deleteUserResult->getCode());
        $this->assertEquals(DeleteUserResult::MESSAGE_SUCCESS, $deleteUserResult->getMessage());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpDeleteException')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function deleteUserException(mixed $exceptionType, mixed $expectedCode, mixed $expectedMessage): void
    {
        $identifier = 'user4574';

        $awsException = m::mock(AwsException::class);
        $awsException->expects('getAwsErrorCode')->andReturn($exceptionType);

        $clientException = new ClientException('null', 0, $awsException);

        $mockClient = m::mock(Client::class);
        $mockClient->expects('deleteUser')->with($identifier)->andThrow($clientException);

        $sut = new CognitoAdapter($mockClient);

        $deleteUserResult = $sut->deleteUser($identifier);

        $this->assertInstanceOf(DeleteUserResult::class, $deleteUserResult);
        $this->assertEquals($expectedCode, $deleteUserResult->getCode());
        $this->assertEquals($expectedMessage, $deleteUserResult->getMessage());
    }

    public static function dpDeleteException(): array
    {
        return [
            'User not in Cognito' => [
                'UserNotFoundException',
                DeleteUserResult::FAILURE_USER_NOT_FOUND,
                DeleteUserResult::MESSAGE_FAILURE_NOT_FOUND,
            ],
            'Any other failure' => [
                'AnyOtherException',
                DeleteUserResult::FAILURE,
                DeleteUserResult::MESSAGE_FAILURE,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderGetIdentityStrings')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getIdentityresultDoesNotContainUppercaseAndCaseConvertsToLowercase(string $identity): void
    {
        $mockClient = m::mock(Client::class);
        $sut = new CognitoAdapter($mockClient);
        $sut->setIdentity($identity);

        $this->assertDoesNotMatchRegularExpression('/[A-Z]+/', $sut->getIdentity());
    }

    public static function dataProviderGetIdentityStrings(): array
    {
        return [
            'Lowercase' => ['testing'],
            'Mixed Case' => ['tEsTiNG'],
            'Uppercase' => ['TESTING'],
            'Uppercase With Special Characters' => ['TESTING@"'],
        ];
    }
}
