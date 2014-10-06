<?php

namespace OlcsTest\Controller\Traits;

use Mockery as m;

/**
 * Tests SubmissionSection Trait
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class SubmissionSectionTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * System under test
     * @var
     */
    protected $sut;

    public function setUp()
    {
        $mockBuilder = $this->getMockBuilder('\Olcs\Controller\Traits\SubmissionSectionTrait');
        $mockBuilder->setMethods(
            ['getParams', 'makeRestCall', 'someValidSection']
        );
        $mockBuilder->setMockClassName(uniqid('mock_SubmissionSectionTrait_'));
        $this->sut = $mockBuilder->getMockForTrait();

    }

    /**
     * Tests createSubmissionSection with no callback method
     *
     * @dataProvider provideSubmissionSectionsNoCallback
     * @param $input
     * @param $expectedResult
     * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
     */
    public function testCreateSubmissionSectionNoCallbackExists($input, $expectedResult)
    {
        $sectionId = 'foo';
        $config = [];
        $mockParams = ['case' => 24];
        $mockRestCallData = 'foo';

        $this->sut->expects($this->once())->method('getParams')->with(array('case'))->willReturn($mockParams);
        $this->sut->expects($this->once())->method('makeRestCall')->with(
            $input['config']['service'],
            'GET',
            ['id' => $mockParams['case']],
            $input['config']['bundle']
        )->willReturn($mockRestCallData);

        $result = $this->sut->createSubmissionSection($input['sectionId'], $input['config']);

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * Tests createSubmissionSection
     *
     * @dataProvider provideSubmissionSectionsWithCallback
     * @param $input
     * @param $expectedResult
     * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
     */
    public function testCreateSubmissionSectionCallbackExists($input)
    {
        $sectionId = 'foo';
        $config = [];
        $mockParams = ['case' => 24];
        $mockRestCallData = $this->getMockRestData();

        $this->sut->expects($this->once())->method('getParams')->with(array('case'))->willReturn($mockParams);
        $this->sut->expects($this->once())->method('makeRestCall')->with(
            $input['config']['service'],
            'GET',
            ['id' => $mockParams['case']],
            $input['config']['bundle']
        )->willReturn($mockRestCallData);

        $result = $this->sut->createSubmissionSection($input['sectionId'], $input['config']);

        $this->assertNotEmpty($result);
    }

    private function getMockRestData()
    {
        return [
            'id' => 999,
            'ecmsNo' => '1234',
            'licence' => [
                'licNo' => '1234',
                'inForceDate' => 'adsf',
                'createdOn' => 'adsf',
                'goodsOrPsv' => [
                    'description' => 'Goods'
                ],
                'licenceType' => [
                    'description' => 'lictype'
                ],
                'status' => [
                    'description' => 'stat'
                ],
                'totAuthVehicles' => 2,
                'totAuthTrailers' => 3,
                'organisation' => [
                    'name' => 'test',
                    'isMlh' => 'Y',
                    'type' => [
                        'description' => 'orgtype'
                    ],
                    'sicCode' => [
                        'description' => 'sic'
                    ]
                ],
                'licenceVehicles' => [
                    '0' => [
                        'specifiedDate' => 'foo',
                        'deletedDate' => null
                    ],
                    '1' => [
                        'specifiedDate' => 'foo',
                        'deletedDate' => null
                    ]
                ]
            ]
        ];
    }



    public function provideSubmissionSectionsNoCallback()
    {
        return [
            [
                [
                    'sectionId' => 'some_section',
                    'config' => [
                        'service' => 'SomeEntity',
                        'bundle' => ['foo' => 'bar']
                    ]
                ],
                ['data' => []]
            ]
        ];
    }



    public function provideSubmissionSectionsWithCallback()
    {
        return [
            [
                [
                    'sectionId' => 'submission_section_casu',
                    'config' => [
                        'service' => 'SomeEntity',
                        'bundle' => ['foo' => 'bar']
                    ]
                ]
            ]
        ];
    }
}
