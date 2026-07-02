<?php

namespace Common\Service\Table\Formatter;

use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Mockery;
use Mockery as m;

class VehicleRegistrationMarkTest extends \PHPUnit\Framework\TestCase
{
    protected $translator;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->sut = new VehicleRegistrationMark($this->translator);

        $this->translator
            ->shouldReceive('translate')
            ->with('application_vehicle-safety_vehicle.table.vrm.interim-marker')
            ->andReturn('TEST_INTERIM_TRANSLATION');
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /** @var Mockery\MockInterface */

    public function testThatNonInterimVrmIsDisplayed(): void
    {
        $data = [
            'vehicle' => ['vrm' => 'TEST_VRM'],
            'interimApplication' => null,
        ];
        $this->assertSame(
            'TEST_VRM',
            $this->sut->format($data, [])
        );
    }

    public function testThatInterimVrmIsDisplayed(): void
    {
        $data = [
            'vehicle' => ['vrm' => 'TEST_VRM'],
            'interimApplication' => ['SOME_TEST_DATA'],
        ];
        $this->assertSame(
            'TEST_VRM (TEST_INTERIM_TRANSLATION)',
            $this->sut->format($data, [])
        );
    }

    public function testThatNonInterimVrmIsDisplayedWhenInterimApplicationIndexIsMissing(): void
    {
        $data = [
            'vehicle' => ['vrm' => 'TEST_VRM'],
        ];
        $this->assertSame(
            'TEST_VRM',
            $this->sut->format($data, [])
        );
    }
}
