<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpCandidatePermit;

use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplication;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplication
 */
class GetListByIrhpApplicationTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = GetListByIrhpApplication::create(
            [
                'irhpApplication' => 2,
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
                'isPreGrant' => false,
                'wantedOnly' => true,
            ]
        );
        static::assertEquals(
            [
                'irhpApplication' => 2,
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
                'sortWhitelist' => [],
                'isPreGrant' => false,
                'wantedOnly' => true
            ],
            $sut->getArrayCopy()
        );
    }
}
