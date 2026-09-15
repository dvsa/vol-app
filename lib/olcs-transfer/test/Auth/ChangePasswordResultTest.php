<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Result\Auth;

use Dvsa\Olcs\Transfer\Result\Auth\ChangePasswordResult;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ChangePasswordResultTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructThrowsInvalidArgumentExceptionWhenCodeIsNotValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('999 is not a valid code');

        new ChangePasswordResult(999);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function fromArrayThrowsInvalidArgumentExceptionWhenCodeIsNotValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('999 is not a valid code');

        ChangePasswordResult::fromArray(['code' => 999, 'message' => '']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('codeDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructCreatesInstanceWhenCodeIsValid(int $code): void
    {
        $this->assertInstanceOf(ChangePasswordResult::class, new ChangePasswordResult($code));
    }

    public static function validCodeDataProvider(): \Iterator
    {
        yield 'Success' => [ChangePasswordResult::SUCCESS, true];
        yield 'Failure' => [ChangePasswordResult::FAILURE, false];
        yield 'Failure new password invalid' => [ChangePasswordResult::FAILURE_NEW_PASSWORD_INVALID, false];
        yield 'Failure client error' => [ChangePasswordResult::FAILURE_CLIENT_ERROR, false];
        yield 'Failure not authorized' => [ChangePasswordResult::FAILURE_NOT_AUTHORIZED, false];
        yield 'Failure old password invalid' => [ChangePasswordResult::FAILURE_OLD_PASSWORD_INVALID, false];
        yield 'Failure password reuse' => [ChangePasswordResult::FAILURE_PASSWORD_REUSE, false];
    }

    public static function codeDataProvider(): \Iterator
    {
        foreach (self::validCodeDataProvider() as $name => [$code, $isValid]) {
            yield $name => [$code];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validCodeDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function isValidReturnsExpectedResponse(int $code, bool $isValid): void
    {
        $result = new ChangePasswordResult($code);

        $this->assertSame($isValid, $result->isValid());
    }
}
