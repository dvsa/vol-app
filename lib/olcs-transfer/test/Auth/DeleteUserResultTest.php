<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Auth;

use Dvsa\Olcs\Transfer\Result\Auth\DeleteUserResult;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DeleteUserResultTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructThrowsInvalidArgumentExceptionWhenCodeIsNotValid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('999 is not a valid code');

        new DeleteUserResult(999);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('codeDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructCreatesInstanceWhenCodeIsValid(int $code)
    {
        $this->assertInstanceOf(DeleteUserResult::class, new DeleteUserResult($code));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validCodeDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function isValidReturnsExpectedResponse(int $code, bool $isValid)
    {
        $result = new DeleteUserResult($code);
        $this->assertSame($isValid, $result->isValid());
    }

    public static function validCodeDataProvider(): \Iterator
    {
        yield 'Success' => [DeleteUserResult::SUCCESS, true];
        yield 'Failure' => [DeleteUserResult::FAILURE, false];
        yield 'Failure user not found' => [DeleteUserResult::FAILURE_USER_NOT_FOUND, false];
    }

    public static function codeDataProvider(): \Iterator
    {
        foreach (self::validCodeDataProvider() as $name => [$code, $isValid]) {
            yield $name => [$code];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('notPresentCodeDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function notPresentReturnsExpectedResponse(int $code, bool $isValid)
    {
        $result = new DeleteUserResult($code);
        $this->assertSame($isValid, $result->isUserNotPresent());
    }

    public static function notPresentCodeDataProvider(): \Iterator
    {
        yield 'Success' => [DeleteUserResult::SUCCESS, false];
        yield 'Failure' => [DeleteUserResult::FAILURE, false];
        yield 'Failure user not found' => [DeleteUserResult::FAILURE_USER_NOT_FOUND, true];
    }
}
