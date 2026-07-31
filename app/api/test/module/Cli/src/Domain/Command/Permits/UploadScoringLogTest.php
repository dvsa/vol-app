<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\Command\Permits;

use Dvsa\Olcs\Cli\Domain\Command\Permits\UploadScoringLog;

/**
 * Upload scoring log test
 *
 */
final class UploadScoringLogTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $sut = UploadScoringLog::create(
            [
                'logContent' => 'TEST'
            ]
        );

        $this->assertEquals('TEST', $sut->getLogContent());
    }
}
