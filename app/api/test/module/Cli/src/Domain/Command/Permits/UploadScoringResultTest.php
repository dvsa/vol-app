<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\Command\Permits;

use Dvsa\Olcs\Cli\Domain\Command\Permits\UploadScoringResult;

/**
 * Upload scoring result test
 *
 */
final class UploadScoringResultTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $sut = UploadScoringResult::create(
            [
                'csvContent' => 'TEST',
                'fileDescription' => 'TEST DESCRIPTION'
            ]
        );

        $this->assertEquals('TEST', $sut->getCsvContent());
        $this->assertEquals('TEST DESCRIPTION', $sut->getFileDescription());
    }
}
