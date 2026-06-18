<?php

namespace CommonTest\Data\Mapper\Lva\TransportManager\Sections;

use Common\Data\Mapper\Lva\TransportManager\Sections\AdditionalInformation;
use Common\Service\Helper\TranslationHelperService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class AdditionalInformationTest extends MockeryTestCase
{
    private $mockTranslator;

    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslationHelperService::class);
        $this->sut = new AdditionalInformation($this->mockTranslator);
    }

    public function testObjectPopulated(): void
    {
        $actual = $this->sut->populate([

            'transportManager' => [
                'documents' => []
            ],
            'additionalInformation' => '__TEST__',

        ]);
        $this->assertInstanceOf(AdditionalInformation::class, $actual);
        foreach (get_object_vars($this->sut) as $property) {
            $this->assertNotEmpty($property);
        }
    }

    public function testObjectPopulatedDocuments(): void
    {
        $actual = $this->sut->populate([
            'application' => ['id' => 1],
            'transportManager' => [
                'documents' => [

                    [
                        'application' => ['id' => 1],
                        'category' => ['id' => 5],
                        'subCategory' => ['id' => 100]
                    ]
                ]
            ],
            'additionalInformation' => '__TEST__',

        ]);
        $this->assertInstanceOf(AdditionalInformation::class, $actual);
        $this->assertEquals([
            'lva-tmverify-details-checkanswer-additionalInformation' => 'Details added',
            'lva-tmverify-details-checkanswer-files' => 1,
        ], $actual->sectionSerialize());
    }
}
