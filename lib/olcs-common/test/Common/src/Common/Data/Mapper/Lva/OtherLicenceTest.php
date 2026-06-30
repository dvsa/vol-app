<?php

/**
 * Other Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\OtherLicence;

/**
 * Licence History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OtherLicenceTest extends \PHPUnit\Framework\TestCase
{
    public function testMapFromResult(): void
    {
        $input = [
            'id' => 'id',
            'version' => 'version',
            'licNo' => 'licNo',
            'willSurrender' => 'willSurrender',
            'holderName' => 'holderName',
            'disqualificationDate' => 'disqualificationDate',
            'disqualificationLength' => 'disqualificationLength',
            'previousLicenceType' => [
                'id' => 'id'
            ]
        ];

        $output = OtherLicence::mapFromResult($input);

        $expected = [
            'id' => 'id',
            'version' => 'version',
            'licNo' => 'licNo',
            'willSurrender' => 'willSurrender',
            'holderName' => 'holderName',
            'disqualificationDate' => 'disqualificationDate',
            'disqualificationLength' => 'disqualificationLength',
            'previousLicenceType' => [
                'id' => 'id'
            ]
        ];

        $this->assertEquals($expected, $output);
    }
}
