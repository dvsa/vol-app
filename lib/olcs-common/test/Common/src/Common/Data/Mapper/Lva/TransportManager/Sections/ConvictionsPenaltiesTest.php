<?php

namespace CommonTest\Data\Mapper\Lva\TransportManager\Sections;

use Common\Data\Mapper\Lva\TransportManager\Sections\ConvictionsPenalties;
use Common\Service\Helper\TranslationHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class ConvictionsPenaltiesTest extends TestCase
{
    private $sut;

    private $mockTranslator;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslationHelperService::class);
        $this->sut = new ConvictionsPenalties($this->mockTranslator);
    }

    public function testObjectPopulated(): void
    {
        $this->mockTranslator->shouldReceive(
            'translateReplace'
        )->with('markup-lva-tmverify-details-checkanswer-answer-convictions', [0 => '__TEST__',1 => '__TEST__'])->once()->andReturn('__TEST__');

        $actual = $this->sut->populate(
            [
                'transportManager' =>
                    [
                        'previousConvictions' =>
                            [
                              [
                                  'categoryText' => '__TEST__',
                                  'convictionDate' => '__TEST__',
                              ]
                            ]
                    ]
            ]
        );
        $this->assertInstanceOf(ConvictionsPenalties::class, $actual);
    }
}
