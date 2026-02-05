<?php

declare(strict_types=1);

/**
 * CreateFeeTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\CreateFee;

/**
 * CreateFeeTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CancelAllInterimFeesTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = CreateFee::create(
            [
                'id' => 121,
                'foo' => 'bar',
            ]
        );

        $this->assertEquals(121, $command->getId());
    }
}
