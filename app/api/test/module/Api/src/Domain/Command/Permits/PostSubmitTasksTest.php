<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\PostSubmitTasks;

/**
 * PostSubmitTasks test
 *
 */
class PostSubmitTasksTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $sut = PostSubmitTasks::create(
            [
                'id' => 100,
                'irhpPermitType' => 1,
            ]
        );

        static::assertEquals(100, $sut->getId());
        static::assertEquals(1, $sut->getIrhpPermitType());
        static::assertEquals(
            [
                'id' => 100,
                'irhpPermitType' => 1,
            ],
            $sut->getArrayCopy()
        );
    }
}
