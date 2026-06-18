<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\RefData;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\View\Helper\Status as StatusHelper;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\View\HelperPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RefDataStatus test
 */
class RefDataStatusTest extends MockeryTestCase
{
    protected $viewHelperManager;

    protected $translator;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->viewHelperManager = m::mock(HelperPluginManager::class);
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->sut = new RefDataStatus($this->viewHelperManager, new RefData($this->translator));
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * tests formatting of ref data statuses
     */
    public function testFormat(): void
    {
        $outputStatus = 'output status';
        $description = 'start description';
        $columnId = 'column id';

        $columnName = 'column name';

        $data = [
            $columnName => [
                'id' => $columnId,
                'description' => 'start description',
            ],
        ];

        $column = [
            'name' => $columnName
        ];

        $statusInput = [
            'id' => $columnId,
            'description' => $description
        ];

        $statusHelper = m::mock(StatusHelper::class);
        $statusHelper->shouldReceive('__invoke')
            ->once()
            ->with($statusInput)
            ->andReturn($outputStatus);

        //this is our status helper
        $this->viewHelperManager->shouldReceive('get')
            ->once()
            ->andReturn($statusHelper);

        $this->translator
            ->shouldReceive('translate')
            ->andReturn($description);

        $this->assertEquals($outputStatus, $this->sut->format($data, $column));
    }
}
