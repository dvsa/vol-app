<?php

namespace CommonTest\Data\Mapper\Lva\TransportManager\Sections;

use Common\Data\Mapper\Lva\TransportManager\Sections\RevokedLicences;
use Common\Service\Helper\TranslationHelperService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class RevokedLicencesTest extends MockeryTestCase
{
    private $mockTranslator;

    private $sut;


    #[\Override]
    protected function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslationHelperService::class);
        $this->sut = new RevokedLicences($this->mockTranslator);
    }

    public function testPopulatedObject(): void
    {
        $actual = $this->sut->populate(
            [
                'transportManager' => [
                    'otherLicences' => [
                    ],
                ]
            ]
        );
        $this->assertInstanceOf(RevokedLicences::class, $actual);

        $this->assertEquals(
            ['lva-tmverify-details-checkanswer-revokedLicences' => 'None Added'],
            $actual->sectionSerialize()
        );
    }

    public function testPopulatedObjectWithLicences(): void
    {
        $this->mockTranslator->shouldReceive(
            'translateReplace'
        )->with(
            'markup-lva-tmverify-details-checkanswer-answer-revokedLicences',
            ['OB123']
        )->once()->andReturn('__TEST__');

        $actual = $this->sut->populate(
            [
                'transportManager' => [
                    'otherLicences' => [
                        ['licNo' => 'OB123']
                    ],

                ]
            ]
        );
        $this->assertInstanceOf(RevokedLicences::class, $actual);

        $this->assertEquals(['lva-tmverify-details-checkanswer-revokedLicences' => '__TEST__'], $actual->sectionSerialize());
    }
}
