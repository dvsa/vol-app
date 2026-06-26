<?php

namespace CommonTest\Data\Mapper\Lva\TransportManager\Sections;

use Common\Data\Mapper\Lva\TransportManager\Sections\Responsibilities;
use Common\Service\Helper\TranslationHelperService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class ResponsibilitiesTest extends MockeryTestCase
{
    private $mockTranslator;

    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslationHelperService::class);
        $this->sut = new Responsibilities($this->mockTranslator);
    }

    public function testObjectPopulated(): void
    {
        $this->mockTranslator->shouldNotReceive(
            'translateReplace'
        );
        $actual = $this->sut->populate(
            [
                'isOwner' => '__TEST__',
                'tmType' => ['description' => '__TEST__'],
            ]
        );
        $this->assertInstanceOf(Responsibilities::class, $actual);
        foreach (get_object_vars($this->sut) as $property) {
            $this->assertNotEmpty($property);
        }
    }
}
