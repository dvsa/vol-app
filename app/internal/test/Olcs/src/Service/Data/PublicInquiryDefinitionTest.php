<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\PublicInquiryDefinition;

/**
 * Class PublicInquiryDefinitionTest
 * @package OlcsTest\Service\Data
 */
class PublicInquiryDefinitionTest extends \PHPUnit_Framework_TestCase
{
    private $definitions = [
        ['id' => 12, 'piDefinitionCategory' => 'Category A', 'description' => 'Description 1'],
        ['id' => 15, 'piDefinitionCategory' => 'Category C', 'description' => 'Description 2'],
    ];

    public function testFetchListOptionsWithGroups()
    {
        $mockLicenceService = $this->createMock('\Common\Service\Data\Licence');
        $mockLicenceService->expects($this->once())
            ->method('fetchLicenceData')
            ->willReturn(['niFlag'=> true, 'goodsOrPsv' => ['id'=>'lcat_gv'], 'trafficArea' => ['id' => 'B']]);

        $sut = new PublicInquiryDefinition();
        $sut->setLicenceService($mockLicenceService);
        $sut->setData('pid', $this->definitions);

        $expected = [
            'Category A' => [
                'label' => 'Category A',
                'options' => [12 => 'Description 1']
            ],
            'Category C' => [
                'label' => 'Category C',
                'options'=>[15 => 'Description 2']
            ]
        ];

        $this->assertEquals($expected, $sut->fetchListOptions([], true));
    }
}
