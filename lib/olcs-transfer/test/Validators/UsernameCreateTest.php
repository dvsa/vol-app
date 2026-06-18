<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\UsernameCreate;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * UsernameCreate Test
 */
class UsernameCreateTest extends MockeryTestCase
{
    private $validator;

    public function setUp(): void
    {
        $this->validator = new UsernameCreate();
    }

    /**
     * @dataProvider provider
     */
    public function testValidator($input, $isValid)
    {
        $outcome = $this->validator->isValid($input);

        $this->assertEquals($isValid, $outcome);
    }

    public function provider()
    {
        return [
            ['0123456789', false],
            ['abcdefghijklmnoprstuvwxyz', true],
            ['ABCDEFGHIJKLMNOPRSTUVWXYZ', false],
            ['#$%\'+-/=?^_.@`|~",:;<>', false],
            ['a¬bs', false],
            ['a!bs', false],
            ['a£bs', false],
            ['a&bs', false],
            ['a*bs', false],
            ['a(bs', false],
            ['a)bs', false],
            ['a bs', false],
            ['0', false],
            ['01', false],
            ['0123456789012345678901234567890123456789', false],
            ['01234567890123456789012345678901234567890', false],
            ['0thisisausernane', false],
            ['thisisausername', true],
            ['thisisausername0', true],
        ];
    }
}
