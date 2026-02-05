<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Cqrs;

use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Service\Cqrs\CommandCreator;
use Dvsa\Olcs\Transfer\Command\Fee\CreateFee;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CommandCreatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CommandCreatorTest extends MockeryTestCase
{
    public function testCreate(): void
    {
        $parameters = [
            'licence' => 7,
            'irhpApplication' => 48,
            'invoicedDate' => '2019-05-01',
            'description' => 'Permit Fee',
            'feeType' => 11045,
            'feeStatus' => Fee::STATUS_OUTSTANDING,
            'quantity' => 12
        ];

        $commandCreator = new CommandCreator();
        $command = $commandCreator->create(CreateFee::class, $parameters);
        $commandArray = $command->getArrayCopy();

        foreach ($parameters as $key => $value) {
            $this->assertArrayHasKey($key, $commandArray, "Array key '{$key}' is missing in the command array.");
            $this->assertEquals($value, $commandArray[$key], "Value for '{$key}' does not match expected value in the command array.");
        }
    }
}
