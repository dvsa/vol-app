<?php

declare(strict_types=1);

/**
 * UpdateVariationCompletion
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateVariationCompletion;

/**
 * UpdateVariationCompletion
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateVariationCompletionTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = UpdateVariationCompletion::create(
            [
                'id' => 563,
                'foo' => 'bar',
                'section' => 'foobar',
            ]
        );

        $this->assertEquals(563, $command->getId());
        $this->assertEquals('foobar', $command->getSection());
    }
}
