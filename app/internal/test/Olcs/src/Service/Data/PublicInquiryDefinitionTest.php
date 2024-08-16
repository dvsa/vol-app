<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\PublicInquiryDefinition;

/**
 * Class PublicInquiryDefinitionTest
 * @package OlcsTest\Service\Data
 */
class PublicInquiryDefinitionTest extends AbstractPublicInquiryDataTestCase
{
    private $definitions = [
        ['id' => 12, 'piDefinitionCategory' => 'Category A', 'description' => 'Description 1'],
        ['id' => 15, 'piDefinitionCategory' => 'Category C', 'description' => 'Description 2'],
    ];

    /** @var PublicInquiryDefinition */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new PublicInquiryDefinition($this->abstractPublicInquiryDataServices);
    }

    public function testFetchListOptionsWithGroups()
    {
        $this->licenceDataService->shouldReceive('getId')
            ->once()
            ->andReturnNull()
            ->shouldReceive('fetchLicenceData')
            ->withNoArgs()
            ->once()
            ->andReturn(
                ['niFlag' => true, 'goodsOrPsv' => ['id' => 'lcat_gv'], 'trafficArea' => ['id' => 'B']]
            );

        $this->sut->setData('pid', $this->definitions);

        $expected = [
            'Category A' => [
                'label' => 'Category A',
                'options' => [12 => 'Description 1']
            ],
            'Category C' => [
                'label' => 'Category C',
                'options' => [15 => 'Description 2']
            ]
        ];

        $this->assertEquals($expected, $this->sut->fetchListOptions([], true));
    }
}
