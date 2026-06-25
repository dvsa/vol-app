<?php

/**
 * Password Callback Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Auth\Service\Auth\Callback;

use Dvsa\Olcs\Auth\Service\Auth\Callback\PasswordCallback;

/**
 * Password Callback Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PasswordCallbackTest extends \PHPUnit\Framework\TestCase
{
    public function testCallback(): void
    {
        $sut = new PasswordCallback('UserPassword', 'ID1', 'test');
        $result = $sut->toArray();
        $expected = [
            'type' => 'PasswordCallback',
            'output' => [['name' => 'prompt', 'value' => 'UserPassword']],
            'input' => [
                [
                    'name' => 'ID1',
                    'value' => 'test'
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }
}
