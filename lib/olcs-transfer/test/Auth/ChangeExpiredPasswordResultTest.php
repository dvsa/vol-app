<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Result\Auth;

use Dvsa\Olcs\Transfer\Result\Auth\ChangeExpiredPasswordResult;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ChangeExpiredPasswordResultTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructThrowsInvalidArgumentExceptionWhenCodeIsNotValid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('999 is not a valid code');

        new ChangeExpiredPasswordResult(999);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('codeDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructCreatesInstanceWhenCodeIsValid(int $code)
    {
        $this->assertInstanceOf(ChangeExpiredPasswordResult::class, new ChangeExpiredPasswordResult($code));
    }

    public static function validCodeDataProvider(): \Iterator
    {
        yield 'Success' => [ChangeExpiredPasswordResult::SUCCESS, true];
        yield 'Success with challenge' => [ChangeExpiredPasswordResult::SUCCESS_WITH_CHALLENGE, true];
        yield 'Failure' => [ChangeExpiredPasswordResult::FAILURE, false];
        yield 'Failure new password invalid' => [ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_INVALID, false];
        yield 'failure not authorized' => [ChangeExpiredPasswordResult::FAILURE_NOT_AUTHORIZED, false];
        yield 'failure client error' => [ChangeExpiredPasswordResult::FAILURE_CLIENT_ERROR, false];
    }

    public static function codeDataProvider(): \Iterator
    {
        foreach (self::validCodeDataProvider() as $name => [$code, $isValid]) {
            yield $name => [$code];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validCodeDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function isValidReturnsExpectedResponse(int $code, bool $isValid)
    {
        $result = new ChangeExpiredPasswordResult($code);

        $this->assertSame($isValid, $result->isValid());
    }
}
