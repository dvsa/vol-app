<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\BusRegStatus;
use Common\View\Helper\Status;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\View\HelperPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see BusRegStatus
 */
class BusRegStatusTest extends MockeryTestCase
{
    public $sut;
    protected $translator;

    protected $viewHelperManager;

    protected $statusHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->viewHelperManager = m::mock(HelperPluginManager::class);
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->statusHelper = m::mock(Status::class);
        $this->sut = new BusRegStatus($this->translator, $this->viewHelperManager);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Tests the formatting for the different possible input array formats
     *
     * @dataProvider formatProvider
     *
     * @param $data
     */
    public function testFormat($data): void
    {
        $regStatus = 'status id';
        $regStatusDesc = 'status description';
        $statusLabel = 'status label';

        $statusArray = [
            'id' => $regStatus,
            'description' => '_TRNSLT_' . $regStatusDesc,
        ];

        $this->translator->shouldReceive('translate')
            ->andReturnUsing(
                static fn($key) => '_TRNSLT_' . $key
            );

        $this->viewHelperManager->shouldReceive('get')->with('status')->andReturn($this->statusHelper);
        $this->statusHelper->shouldReceive('__invoke')
            ->with($statusArray)
            ->andReturn($statusLabel);

        $this->assertEquals($statusLabel, $this->sut->format($data, []));
    }

    /**
     * Data provider for testFormat
     *
     * @return array
     */
    public function formatProvider()
    {
        $regStatus = 'status id';
        $regStatusDesc = 'status description';

        $busSearchViewFormat = [
            'busRegStatus' => $regStatus,
            'busRegStatusDesc' => $regStatusDesc
        ];

        $txcInboxFormat = [
            'status' => [
                'id' => $regStatus,
                'description' => $regStatusDesc
            ]
        ];

        $ebsrSubmissionFormat = [
            'busReg' => $txcInboxFormat
        ];

        return [
            [$busSearchViewFormat],
            [$txcInboxFormat],
            [$ebsrSubmissionFormat],
        ];
    }
}
