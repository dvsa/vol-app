<?php

namespace OlcsTest\Controller\Traits;

/**
 * Class HearingAppealControllerTrait
 * @package OlcsTest\Controller\Traits
 */
class HearingAppealControllerTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAppealData()
    {
        $caseId = '1';

        $appealResult = [
            'Results' => [
                0 => [
                    'id' => '1',
                    'name' => 'craig'
                ],
                1 => [
                    'id' => '2',
                    'name' => 'an-other'
                ],
            ]
        ];

        $methodReturn = [
            'id' => '1',
            'name' => 'craig'
        ];

        $mockBuilder = $this->getMockBuilder('Olcs\Controller\Traits\HearingAppealControllerTrait');
        $mockBuilder->setMethods(
            ['makeRestCall']
        );
        $mockBuilder->setMockClassName(uniqid('mock_HearingAppealControllerTrait_'));
        $sut = $mockBuilder->getMockForTrait();

        $sut->expects($this->once())->method('makeRestCall')
            ->with('Appeal', 'GET', ['case' => $caseId], $sut->getAppealDataBundle())
            ->will($this->returnValue($appealResult));

        $this->assertEquals($methodReturn, $sut->getAppealData($caseId));
    }

    public function testGetStayData()
    {
        $caseId = '1';

        $stayResult = [
            'Results' => [
                0 => [
                    'id' => '1',
                    'name' => 'craig',
                    'stayType' => ['id' => 'id1']
                ],
                1 => [
                    'id' => '2',
                    'name' => 'an-other',
                    'stayType' => ['id' => 'id2']
                ]
            ]
        ];

        $stayRecords = [
            'id1' => [
                0 => [
                    'id' => '1',
                    'name' => 'craig',
                    'stayType' => ['id' => 'id1']
                ]
            ],
            'id2' => [
                0 => [
                    'id' => '2',
                    'name' => 'an-other',
                    'stayType' => ['id' => 'id2']
                ]
            ]
        ];

        $mockBuilder = $this->getMockBuilder('Olcs\Controller\Traits\HearingAppealControllerTrait');
        $mockBuilder->setMethods(
            ['makeRestCall']
        );
        $mockBuilder->setMockClassName(uniqid('mock_HearingAppealControllerTrait_'));
        $sut = $mockBuilder->getMockForTrait();

        $sut->expects($this->once())->method('makeRestCall')
        ->with('Stay', 'GET', ['case' => $caseId], $sut->getStayRecordBundle())
        ->will($this->returnValue($stayResult));

        $this->assertEquals($stayRecords, $sut->getStayData($caseId));
    }
}
