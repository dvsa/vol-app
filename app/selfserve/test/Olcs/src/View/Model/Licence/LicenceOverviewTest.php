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
class LicenceOverviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test constructor with set variables
     *
     * @group        licenceOverview
     */
    public function testSetVariables()
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'showExpiryWarning' => 'SHOWEXPIRYWARNING',
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->licenceId, 1);
        $this->assertEquals($overview->startDate, '2014-01-01');
        $this->assertEquals($overview->renewalDate, '2015-01-01');
        $this->assertEquals($overview->status, 'status');
        $this->assertEquals($overview->showExpiryWarning, 'SHOWEXPIRYWARNING');
        $this->assertEquals($overview->returnDefaultInfoBoxLinks(), $this->returnExpectedInfoBoxLinks());
    }

    public function testSetVariablesIsExpired()
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'isExpired' => true,
            'isExpiring' => true,
            'showExpiryWarning' => 'SHOWEXPIRYWARNING',
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->licenceId, 1);
        $this->assertEquals($overview->startDate, '2014-01-01');
        $this->assertEquals($overview->renewalDate, '2015-01-01');
        $this->assertEquals($overview->status, 'licence.status.expired');
        $this->assertEquals($overview->returnDefaultInfoBoxLinks(), $this->returnExpectedInfoBoxLinks());
    }

    public function testSetVariablesIsExpiring()
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'isExpired' => false,
            'isExpiring' => true,
            'showExpiryWarning' => 'SHOWEXPIRYWARNING',
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->licenceId, 1);
        $this->assertEquals($overview->startDate, '2014-01-01');
        $this->assertEquals($overview->renewalDate, '2015-01-01');
        $this->assertEquals($overview->status, 'licence.status.expiring');
        $this->assertEquals($overview->returnDefaultInfoBoxLinks(), $this->returnExpectedInfoBoxLinks());
    }

    public function testSetVariablesContinuationDetailId()
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
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->continuationDetailId, 12345);
        $this->assertAttributeEquals($this->returnExpectedInfoBoxLinks(), 'infoBoxLinks', $overview);
    }

    /**
     * @dataProvider dpAddInfoBoxLinks
     */
    public function testAddInfoBoxLinks($additionalLinks, $expectedLinks)
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
        ];

        $overview = new LicenceOverview($data);
        $overview->addInfoBoxLinks($additionalLinks);
        $this->assertAttributeEquals($expectedLinks, 'infoBoxLinks', $overview);
    }

    public function testSetInfoBoxLinks()
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
        ];

        $overview = new LicenceOverview($data);
        $overview->setInfoBoxLinks();
        $this->assertEquals($overview->infoBoxLinks, $this->returnExpectedInfoBoxLinks());
    }

    public function returnExpectedInfoBoxLinks()
    {
        return
            [
                [
                    'linkUrl' => [
                        'route' => 'licence-print',
                        'params' => [],
                        'options' => [],
                        'reuseMatchedParams' => true
                    ],
                    'linkText' => 'licence.print'
                ],
            ];
    }

    public function dpAddInfoBoxLinks()
    {
        return [
            [
                'additionalInfoBoxLinks' => [
                    'linkUrl' => [
                        'route' => 'additional-route',
                        'params' => [],
                        'options' => [],
                        'reuseMatchedParams' => true
                    ],
                    'linkText' => 'additional-link-text'
                ],
                'expectedInfoBoxLinks' => [
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
                            'route' => 'additional-route',
                            'params' => [],
                            'options' => [],
                            'reuseMatchedParams' => true
                        ],
                        'linkText' => 'additional-link-text'

                    ]
                ],

            ],
            [
                'additionalInfoBoxLinks' => [],
                'expectedInfoBoxLinks' => [
                    [
                        'linkUrl' => [
                            'route' => 'licence-print',
                            'params' => [],
                            'options' => [],
                            'reuseMatchedParams' => true
                        ],
                        'linkText' => 'licence.print'
                    ]
                ]
            ]
        ];
    }
}
