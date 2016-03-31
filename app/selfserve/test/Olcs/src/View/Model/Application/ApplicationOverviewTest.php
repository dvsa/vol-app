<?php

/**
 * Application Overview Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\View\Model\Application;

use Olcs\View\Model\Application\ApplicationOverview;

/**
 * Application Overview Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationOverviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test constructor with set variables
     *
     * @group applicationOverview
     */
    public function testSetVariables()
    {
        $data = [
            'id' => 1,
            'createdOn' => '2014-01-01',
            'status' => ['id' => 'status'],
            'submissionForm' => 'form',
            'receivedDate' => '2014-01-01',
            'targetCompletionDate' => '2014-01-01',
        ];
        $overview = new ApplicationOverview($data);
        $this->assertEquals($overview->applicationId, 1);
        $this->assertEquals($overview->createdOn, '01 January 2014');
        $this->assertEquals($overview->status, 'status');
        $this->assertEquals($overview->receivedDate, '2014-01-01');
        $this->assertEquals($overview->completionDate, '2014-01-01');
    }

    /**
     * @dataProvider progressProvider
     */
    public function testProgressCalculation($sections, $expectedX, $expectedY)
    {
        $data = [
            'id'                   => 1,
            'idIndex'              => 'application',
            'createdOn'            => '2015-01-14',
            'status'               => ['id' => 'status'],
            'submissionForm'       => 'form',
            'receivedDate'         => '2015-01-14',
            'targetCompletionDate' => '2015-01-14',
            'licence'              => [
                'organisation' => [
                    'type' => [
                        'id' => 'org_typ_rc'
                    ]
                ]
            ]
        ];

        $overview = new ApplicationOverview($data, $sections);

        $variables = $overview->getVariables();
        $this->assertEquals($expectedX, $variables['progressX']);
        $this->assertEquals($expectedY, $variables['progressY']);
    }

    public function progressProvider()
    {
        return [
            'no sections' => [[], 0, 0],
            '1 section' => [
                [
                    'type_of_licence' => ['enabled' => true, 'complete' => false],
                ],
                0,
                1
            ],
            '2 sections' => [
                [
                    'type_of_licence' => ['enabled' => true, 'complete' => false],
                    'business_type'   => ['enabled' => true, 'complete' => false],
                ],
                0,
                2
            ],
            '2 sections 1 complete' => [
                [
                    'type_of_licence' => ['enabled' => true, 'complete' => false],
                    'business_type'   => ['enabled' => true, 'complete' => true],
                ],
                1,
                2
            ],
            'all complete' => [
                [
                    'type_of_licence'  => ['enabled' => true, 'complete' => true],
                    'business_type'    => ['enabled' => true, 'complete' => true],
                    'business_details' => ['enabled' => true, 'complete' => true],
                ],
                3,
                3
            ],
        ];
    }
}
