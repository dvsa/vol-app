<?php

/**
 * Name Callback Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Auth\Service\Auth\Callback;

use Dvsa\Olcs\Auth\Service\Auth\Callback\NameCallback;

/**
 * Name Callback Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class NameCallbackTest extends \PHPUnit\Framework\TestCase
{
    public function testCallback(): void
    {
        $sut = new NameCallback('Username', 'ID1', 'test');
        $result = $sut->toArray();
        $expected = [
            'type' => 'NameCallback',
            'output' => [['name' => 'prompt', 'value' => 'Username']],
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
