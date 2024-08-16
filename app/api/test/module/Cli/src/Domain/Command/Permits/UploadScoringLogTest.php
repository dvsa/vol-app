<?php

namespace Dvsa\OlcsTest\Cli\Domain\Command\Permits;

use Dvsa\Olcs\Cli\Domain\Command\Permits\UploadScoringLog;

/**
 * Upload scoring log test
 *
 */
class UploadScoringLogTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = UploadScoringLog::create(
            [
                'logContent' => 'TEST'
            ]
        );

        static::assertEquals('TEST', $sut->getLogContent());
    }
}
