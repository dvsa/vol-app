<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Cli\Domain\CommandHandler\CreateViExtractFiles;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;

/**
 * Create VI Extract Files Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateViExtractFilesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateViExtractFiles();
        $this->mockRepo('ViOcView', Repository\ViOcView::class);
        $this->mockRepo('ViOpView', Repository\ViOpView::class);
        $this->mockRepo('ViTnmView', Repository\ViTnmView::class);
        $this->mockRepo('ViVhlView', Repository\ViVhlView::class);

        $this->mockedSmServices['config'] = [
            'vi_extract_files' => [
                'export_path' => '/tmp/ViExtract'
            ]
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $params = [
            'op' => true,
            'oc' => true,
            'tnm' => true,
            'vhl' => true,
            'all' => null,
            'path' => '/tmp'
        ];

        $this->repoMap['ViOpView']
            ->shouldReceive('fetchForExport')
            ->once()
            ->andReturn([['line' => 'foo', 'licId' => 3]])
            ->shouldReceive('clearLicencesViIndicators')
            ->with([['licId' => 3]])
            ->once()
            ->getMock();

        $this->repoMap['ViOcView']
            ->shouldReceive('fetchForExport')
            ->once()
            ->andReturn([['line' => 'bar', 'ocId' => 1]])
            ->shouldReceive('clearOcViIndicators')
            ->with([['ocId' => 1]])
            ->once()
            ->getMock();

        $this->repoMap['ViTnmView']
            ->shouldReceive('fetchForExport')
            ->once()
            ->andReturn([['line' => 'cake', 'tradingNameId' => 2]])
            ->shouldReceive('clearTradingNamesViIndicators')
            ->with([['tradingNameId' => 2]])
            ->once()
            ->getMock();

        $this->repoMap['ViVhlView']->shouldReceive('fetchForExport')
            ->once()->andReturn([]);

        $response = $this->sut->handleCommand(\Dvsa\Olcs\Cli\Domain\Command\CreateViExtractFiles::create($params));

        $expected = [
            'id' => [],
            'messages' => [
                'Found 1 record(s) for Operating Centres',
                '1 record(s) saved for Operating Centres',
                'VI flags cleared',
                'Found 1 record(s) for Operators',
                '1 record(s) saved for Operators',
                'VI flags cleared',
                'Found 1 record(s) for Trading Names',
                '1 record(s) saved for Trading Names',
                'VI flags cleared',
                'Found 0 record(s) for Vehicles',
                'Empty file written for Vehicles'
            ]
        ];

        $this->assertEquals($expected, $response->toArray());

        $datetime = (new DateTime())->format('YmdHis');
        $this->assertFileExists(sprintf('%s/tanopc%s.dat', $params['path'], $datetime));
        $this->assertFileExists(sprintf('%s/tanopo%s.dat', $params['path'], $datetime));
        $this->assertFileExists(sprintf('%s/tantnm%s.dat', $params['path'], $datetime));
        $this->assertFileExists(sprintf('%s/tanveh%s.dat', $params['path'], $datetime));
    }
}
