<?php

/**
 * UsernameTest
 */

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Username;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Username Test
 */
final class UsernameTest extends MockeryTestCase
{
    private $validator;

    #[\Override]
    public function setUp(): void
    {
        $this->validator = new Username();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testValidator($input, $isValid)
    {
        $outcome = $this->validator->isValid($input);

        $this->assertEquals($isValid, $outcome);
    }

    public static function provider(): \Iterator
    {
        yield ['0123456789', true];
        yield ['abcdefghijklmnoprstuvwxyz', true];
        yield ['ABCDEFGHIJKLMNOPRSTUVWXYZ', true];
        yield ['#$%\'+-/=?^_.@`|~",:;<>', true];
        yield ['a¬b', false];
        yield ['a!b', false];
        yield ['a£b', false];
        yield ['a&b', false];
        yield ['a*b', false];
        yield ['a(b', false];
        yield ['a)b', false];
        yield ['a b', false];
        yield ['0', false];
        yield ['01', true];
        yield ['0123456789012345678901234567890123456789', true];
        yield ['01234567890123456789012345678901234567890', false];
    }
}
