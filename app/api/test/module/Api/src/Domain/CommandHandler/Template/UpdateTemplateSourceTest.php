<?php

declare(strict_types=1);

/**
 * Update Template Source Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Template;

use Dvsa\Olcs\Api\Domain\CommandHandler\Template\UpdateTemplateSource as Sut;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Template as TemplateRepo;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Transfer\Command\Template\UpdateTemplateSource as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use RuntimeException;

/**
 * Update Template Source Test
 */
class UpdateTemplateSourceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();

        $this->mockRepo('Template', TemplateRepo::class);

        $this->mockedSmServices = [
            'TemplateTwigRenderer' => m::mock(TwigRenderer::class)
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $templateId = 7;
        $source = '{{key1}} {{key2}}';

        $command = Cmd::create(
            [
                'id' => $templateId,
                'source' => $source
            ]
        );

        $dataset1 = [
            'key1' => 'dataset1value1',
            'key2' => 'dataset1value2',
        ];

        $dataset2 = [
            'key1' => 'dataset2value1',
            'key2' => 'dataset2value2',
        ];

        $datasets = [
            'Dataset 1' => $dataset1,
            'Dataset 2' => $dataset2,
        ];

        $this->mockedSmServices['TemplateTwigRenderer']->shouldReceive('renderString')
            ->with($source, $dataset1)
            ->once();

        $this->mockedSmServices['TemplateTwigRenderer']->shouldReceive('renderString')
            ->with($source, $dataset2)
            ->once();

        $template = m::mock(Template::class);
        $template->shouldReceive('getDecodedTestData')
            ->andReturn($datasets);
        $template->shouldReceive('setSource')
            ->once()
            ->with($source)
            ->globally()
            ->ordered();

        $this->repoMap['Template']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($template);

        $this->repoMap['Template']->shouldReceive('save')
            ->with($template)
            ->once()
            ->globally()
            ->ordered();

        $result = $this->sut->handleCommand($command);

        $expectedMessages = ['Template source updated'];
        $this->assertEquals($expectedMessages, $result->getMessages());
    }

    public function testValidationExceptionOnTemplateRenderingFailure(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unable to render template content with dataset Dataset 2: Something went wrong');

        $templateId = 7;
        $source = '{{key1}} {{key2}}';

        $command = Cmd::create(
            [
                'id' => $templateId,
                'source' => $source
            ]
        );

        $dataset1 = [
            'key1' => 'dataset1value1',
            'key2' => 'dataset1value2',
        ];

        $dataset2 = [
            'key1' => 'dataset2value1',
            'key2' => 'dataset2value2',
        ];

        $datasets = [
            'Dataset 1' => $dataset1,
            'Dataset 2' => $dataset2,
        ];

        $this->mockedSmServices['TemplateTwigRenderer']->shouldReceive('renderString')
            ->with($source, $dataset1)
            ->once();

        $this->mockedSmServices['TemplateTwigRenderer']->shouldReceive('renderString')
            ->with($source, $dataset2)
            ->once()
            ->andThrow(new RuntimeException('Something went wrong'));

        $template = m::mock(Template::class);
        $template->shouldReceive('getDecodedTestData')
            ->andReturn($datasets);

        $this->repoMap['Template']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($template);

        $this->sut->handleCommand($command);
    }
}
