<?php

namespace CommonTest\View\Helper;

use Common\RefData;
use Common\View\Helper\TransportManagerApplicationStatus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\View\Renderer\RendererInterface;

/**
 * @covers \Common\View\Helper\TransportManagerApplicationStatus
 */
class TransportManagerApplicationStatusTest extends MockeryTestCase
{
    /** @var TransportManagerApplicationStatus */
    private $sut;

    /** @var  m\MockInterface */
    private $mockView;

    /**
     * Setup the view helper
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->mockView = m::mock(RendererInterface::class);

        $this->sut = new TransportManagerApplicationStatus();
        $this->sut->setView($this->mockView);
    }

    /**
     * @return string[][]
     *
     * @psalm-return array{0: list{'orange', 'tmap_st_awaiting_signature'}, 1: list{'red', 'tmap_st_incomplete'}, 2: list{'green', 'tmap_st_operator_signed'}, 3: list{'green', 'tmap_st_postal_application'}, 4: list{'orange', 'tmap_st_tm_signed'}, 5: list{'green', 'tmap_st_received'}, invalidStatus: list{'', 'foo'}}
     */
    public function dataProviderRender(): array
    {
        return [
            ['orange', RefData::TMA_STATUS_AWAITING_SIGNATURE],
            ['red', RefData::TMA_STATUS_INCOMPLETE],
            ['green', RefData::TMA_STATUS_OPERATOR_SIGNED],
            ['green', RefData::TMA_STATUS_POSTAL_APPLICATION],
            ['orange', RefData::TMA_STATUS_TM_SIGNED],
            ['green', RefData::TMA_STATUS_RECEIVED],
            'invalidStatus' => ['', 'foo'],
        ];
    }

    /**
     * @dataProvider dataProviderRender
     */
    public function testInvoke($expectedClass, $status): void
    {
        $this->mockView
            ->shouldReceive('translate')
            ->once()
            ->andReturnUsing(
                static fn($desciption) => '_TRANSL_' . $desciption
            );

        static::assertEquals(
            '<strong class="govuk-tag govuk-tag--' . $expectedClass . '">_TRANSL_' . $status . '</strong>',
            $this->sut->__invoke($status, $status)
        );
    }

    public function testRenderDescEmpty(): void
    {
        $sut = new TransportManagerApplicationStatus();

        static::assertEquals('', $sut->render(null, ''));
    }
}
