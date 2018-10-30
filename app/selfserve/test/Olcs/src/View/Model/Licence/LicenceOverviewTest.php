<?php

/**
 * Licence Overview Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\View\Model\Licence;

use Olcs\View\Model\Licence\LicenceOverview;

/**
 * Licence Overview Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceOverviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test constructor with set variables
     *
     * @group licenceOverview
     * @dataProvider dpReturnInfoBoxLinks
     */
    public function testSetVariables($isSurrenderAllowed, $links)
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'showExpiryWarning' => 'SHOWEXPIRYWARNING',
            'isLicenceSurrenderAllowed' => $isSurrenderAllowed,
            'infoBoxLinks' => $links,
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->licenceId, 1);
        $this->assertEquals($overview->startDate, '2014-01-01');
        $this->assertEquals($overview->renewalDate, '2015-01-01');
        $this->assertEquals($overview->status, 'status');
        $this->assertEquals($overview->showExpiryWarning, 'SHOWEXPIRYWARNING');
        $this->assertEquals($overview->infoBoxLinks, $links);
    }

    /**
     * @dataProvider dpReturnInfoBoxLinks
     */
    public function testSetVariablesIsExpired($isSurrenderAllowed, $links)
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'isExpired' => true,
            'isExpiring' => true,
            'showExpiryWarning' => 'SHOWEXPIRYWARNING',
            'isLicenceSurrenderAllowed' => $isSurrenderAllowed,
            'infoBoxLinks' => $links,
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->licenceId, 1);
        $this->assertEquals($overview->startDate, '2014-01-01');
        $this->assertEquals($overview->renewalDate, '2015-01-01');
        $this->assertEquals($overview->status, 'licence.status.expired');
        $this->assertEquals($overview->infoBoxLinks, $links);
    }

    /**
     * @dataProvider dpReturnInfoBoxLinks
     */
    public function testSetVariablesIsExpiring($isSurrenderAllowed, $links)
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'isExpired' => false,
            'isExpiring' => true,
            'showExpiryWarning' => 'SHOWEXPIRYWARNING',
            'isLicenceSurrenderAllowed' => $isSurrenderAllowed,
            'infoBoxLinks' => $links,
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->licenceId, 1);
        $this->assertEquals($overview->startDate, '2014-01-01');
        $this->assertEquals($overview->renewalDate, '2015-01-01');
        $this->assertEquals($overview->status, 'licence.status.expiring');
        $this->assertEquals($overview->infoBoxLinks, $links);
    }

    /**
     * @dataProvider dpReturnInfoBoxLinks
     */
    public function testSetVariablesContinuationDetailId($isSurrenderAllowed, $links)
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'isExpired' => false,
            'isExpiring' => true,
            'showExpiryWarning' => 'SHOWEXPIRYWARNING',
            'continuationMarker' => ['id' => 12345],
            'isLicenceSurrenderAllowed' => $isSurrenderAllowed,
            'infoBoxLinks' => $links,
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->continuationDetailId, 12345);
        $this->assertEquals($overview->infoBoxLinks, $links);
    }

    public function dpReturnInfoBoxLinks()
    {
        return [
            [
                'isSurrenderAllowed' => false,
                'links' => [
                    [
                        'linkUrl' => [
                            'route' => 'licence-print',
                            'params' => [],
                            'options' => [],
                            'reuseMatchedParams' => true
                        ],
                        'linkText' => 'licence.print'
                    ],
                ]
            ],
            [
                'isSurrenderAllowed' => true,
                'links' => [
                    [
                        'linkUrl' => [
                            'route' => 'licence-print',
                            'params' => [],
                            'options' => [],
                            'reuseMatchedParams' => true
                        ],
                        'linkText' => 'licence.print'
                    ],
                    [
                        'linkUrl' => [
                            'route' => 'surrender-licence-start',
                            'params' => [],
                            'options' => [],
                            'reuseMatchedParams' => true
                        ],
                        'linkText' => 'licence.apply-to-surrender'
                    ],
                ]
            ]
        ];
    }
}
