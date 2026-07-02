<?php

namespace CommonTest\Data\Mapper\Lva\TransportManager\Sections;

use Common\Data\Mapper\Lva\TransportManager\Sections\OtherEmployment;
use Common\Service\Helper\TranslationHelperService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class OtherEmploymentsTest extends MockeryTestCase
{
    private $mockTranslator;

    private $sut;


    #[\Override]
    protected function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslationHelperService::class);
        $this->sut = new OtherEmployment($this->mockTranslator);
    }

    public function testObjectPopulated(): void
    {
        $actual = $this->sut->populate(
            [
                'transportManager' => [
                    'employments' => []
                ]
            ]
        );

        $this->assertInstanceOf(OtherEmployment::class, $actual);
        $this->assertEquals(['lva-tmverify-details-checkanswer-employments' => 'None Added'], $actual->sectionSerialize());
    }

    public function testObjectPopulatedWithEmployment(): void
    {
        $this->mockTranslator->shouldReceive(
            'translateReplace'
        )->with('markup-lva-tmverify-details-checkanswer-answer-otherEmployments', ['__TEST__'])->times(3)->andReturn('__TEST__');

        $this->mockTranslator->shouldReceive(
            'translateReplace'
        )->with('markup-lva-tmverify-details-checkanswer-answer-otherEmployments-more', [1])->once()->andReturn('__TEST__');

        $actual = $this->sut->populate(
            [
                'transportManager' => [
                    'employments' => [
                        0 => [
                            'employerName' => '__TEST__',
                            'createdOn' => 1,
                        ],
                        1 => [
                            'employerName' => '__TEST__',
                            'createdOn' => 3,
                        ],
                        2 => [
                            'employerName' => '__TEST__',
                            'createdOn' => 2,
                        ],
                        3 => [
                            'employerName' => '__TEST__',
                            'createdOn' => 4,
                        ]
                    ]
                ]
            ]
        );

        $this->assertInstanceOf(OtherEmployment::class, $actual);
        $this->assertEquals(['lva-tmverify-details-checkanswer-employments' => '__TEST____TEST____TEST____TEST__'], $actual->sectionSerialize());
    }
}
