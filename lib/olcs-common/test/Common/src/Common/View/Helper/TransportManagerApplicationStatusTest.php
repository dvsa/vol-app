<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\RefData;
use Common\View\Helper\TransportManagerApplicationStatus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\View\Renderer\RendererInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\View\Helper\TransportManagerApplicationStatus::class)]
final class TransportManagerApplicationStatusTest extends MockeryTestCase
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
     * @return \Iterator<(int | string), array<string>>
     *
     * @psalm-return array{0: list{'orange', 'tmap_st_awaiting_signature'}, 1: list{'red', 'tmap_st_incomplete'}, 2: list{'green', 'tmap_st_operator_signed'}, 3: list{'green', 'tmap_st_postal_application'}, 4: list{'orange', 'tmap_st_tm_signed'}, 5: list{'green', 'tmap_st_received'}, invalidStatus: list{'', 'foo'}}
     */
    public static function dataProviderRender(): \Iterator
    {
        yield ['orange', RefData::TMA_STATUS_AWAITING_SIGNATURE];
        yield ['red', RefData::TMA_STATUS_INCOMPLETE];
        yield ['green', RefData::TMA_STATUS_OPERATOR_SIGNED];
        yield ['green', RefData::TMA_STATUS_POSTAL_APPLICATION];
        yield ['orange', RefData::TMA_STATUS_TM_SIGNED];
        yield ['green', RefData::TMA_STATUS_RECEIVED];
        yield 'invalidStatus' => ['', 'foo'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderRender')]
    public function testInvoke($expectedClass, $status): void
    {
        $this->mockView
            ->shouldReceive('translate')
            ->once()
            ->andReturnUsing(
                static fn($desciption) => '_TRANSL_' . $desciption
            );

        $this->assertEquals('<strong class="govuk-tag govuk-tag--' . $expectedClass . '">_TRANSL_' . $status . '</strong>', $this->sut->__invoke($status, $status));
    }

    public function testRenderDescEmpty(): void
    {
        $sut = new TransportManagerApplicationStatus();

        $this->assertEquals('', $sut->render(null, ''));
    }
}
