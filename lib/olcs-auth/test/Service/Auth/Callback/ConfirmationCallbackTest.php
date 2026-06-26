<?php

/**
 * Confirmation Callback Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Auth\Service\Auth\Callback;

use Dvsa\Olcs\Auth\Service\Auth\Callback\ConfirmationCallback;

/**
 * Confirmation Callback Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConfirmationCallbackTest extends \PHPUnit\Framework\TestCase
{
    public function testCallback(): void
    {
        $sut = new ConfirmationCallback('foo');
        $result = $sut->toArray();
        $expected = [
            'type' => 'ConfirmationCallback',
            'output' => [
                ['name' => 'prompt', 'value' => ''],
                ['name' => 'messageType', 'value' => 0],
                ['name' => 'options', 'value' => ['Submit', 'Cancel']],
                ['name' => 'optionType', 'value' => -1],
                ['name' => 'defaultOption', 'value' => 0]
            ],
            'input' => [['name' => 'foo', 'value' => 0]]
        ];

        $this->assertEquals($expected, $result);
    }
}
