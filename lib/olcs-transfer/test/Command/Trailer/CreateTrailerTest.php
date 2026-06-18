<?php

namespace Dvsa\OlcsTest\Transfer\Command\Trailer;

use Dvsa\Olcs\Transfer\Command\Trailer\CreateTrailer;
use PHPUnit\Framework\TestCase;

/**
 * Create trailer test
 */
class CreateTrailerTest extends TestCase
{
    public function testStructure()
    {
        $trailerNo = 'TRL012';
        $isLongerSemiTrailer = 'Y';
        $licence = 773;
        $specifiedDate = '2022-05-23';

        $data = [
            'trailerNo' => $trailerNo,
            'isLongerSemiTrailer' => $isLongerSemiTrailer,
            'licence' => $licence,
            'specifiedDate' => $specifiedDate,
        ];

        $command = CreateTrailer::create($data);

        $this->assertEquals($trailerNo, $command->getTrailerNo());
        $this->assertEquals($isLongerSemiTrailer, $command->getIsLongerSemiTrailer());
        $this->assertEquals($licence, $command->getLicence());
        $this->assertEquals($specifiedDate, $command->getSpecifiedDate());
    }
}
