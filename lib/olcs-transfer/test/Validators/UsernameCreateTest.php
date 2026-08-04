<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\UsernameCreate;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * UsernameCreate Test
 */
final class UsernameCreateTest extends MockeryTestCase
{
    private $validator;

    #[\Override]
    public function setUp(): void
    {
        $this->validator = new UsernameCreate();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testValidator($input, $isValid)
    {
        $outcome = $this->validator->isValid($input);

        $this->assertEquals($isValid, $outcome);
    }

    public static function provider(): \Iterator
    {
        yield ['0123456789', false];
        yield ['abcdefghijklmnoprstuvwxyz', true];
        yield ['ABCDEFGHIJKLMNOPRSTUVWXYZ', false];
        yield ['#$%\'+-/=?^_.@`|~",:;<>', false];
        yield ['a¬bs', false];
        yield ['a!bs', false];
        yield ['a£bs', false];
        yield ['a&bs', false];
        yield ['a*bs', false];
        yield ['a(bs', false];
        yield ['a)bs', false];
        yield ['a bs', false];
        yield ['0', false];
        yield ['01', false];
        yield ['0123456789012345678901234567890123456789', false];
        yield ['01234567890123456789012345678901234567890', false];
        yield ['0thisisausernane', false];
        yield ['thisisausername', true];
        yield ['thisisausername0', true];
    }
}
