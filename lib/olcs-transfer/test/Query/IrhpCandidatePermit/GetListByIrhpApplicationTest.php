<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\IrhpCandidatePermit;

use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplication;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplication::class)]
final class GetListByIrhpApplicationTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals([
            'irhpApplication' => 2,
            'page' => 1,
            'limit' => 10,
            'sort' => 'id',
            'order' => 'ASC',
            'sortWhitelist' => [],
            'isPreGrant' => false,
            'wantedOnly' => true
        ], $sut->getArrayCopy());
    }
}
