<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\ConditionsUndertakingsType;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class ConditionsUndertakingsTypeTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ConditionsUndertakingsTypeTest extends TestCase
{
    public $sut;
    protected $translator;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->sut = new ConditionsUndertakingsType($this->translator);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormatNoS4(): void
    {
        $data = [
            'conditionType' => [
                'description' => 'DESCRIPTION'
            ],
            's4' => null
        ];
        $column = null;

        $this->assertSame('DESCRIPTION', $this->sut->format($data, $column));
    }

    public function testFormatWithS4(): void
    {
        $data = [
            'conditionType' => [
                'description' => 'DESCRIPTION'
            ],
            's4' => ['FOO']
        ];
        $column = null;
        $this->translator->shouldReceive('translate')->with('(Schedule 4/1)')->once()->andReturn('TRANSLATED');

        $this->assertSame('DESCRIPTION<br>TRANSLATED', $this->sut->format($data, $column));
    }
}
