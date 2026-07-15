<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\PostSubmitTasks;

/**
 * PostSubmitTasks test
 *
 */
final class PostSubmitTasksTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $sut = PostSubmitTasks::create(
            [
                'id' => 100,
                'irhpPermitType' => 1,
            ]
        );

        $this->assertEquals(100, $sut->getId());
        $this->assertEquals(1, $sut->getIrhpPermitType());
        $this->assertEquals([
            'id' => 100,
            'irhpPermitType' => 1,
        ], $sut->getArrayCopy());
    }
}
