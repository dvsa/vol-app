<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\Template\PreviewTemplateSource;
use Dvsa\Olcs\Api\Domain\Repository\Template as TemplateRepo;
use Dvsa\Olcs\Api\Service\Template\StrategySelectingViewRenderer;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Transfer\Query\Template\PreviewTemplateSource as PreviewTemplateSourceQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use RuntimeException;

class PreviewTemplateSourceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PreviewTemplateSource();

        $this->mockRepo('Template', TemplateRepo::class);

        $this->mockedSmServices = [
            'TemplateStrategySelectingViewRenderer' => m::mock(StrategySelectingViewRenderer::class),
            'TemplateTwigRenderer' => m::mock(TwigRenderer::class),
        ];

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $templateId = 6;
        $locale = 'en_GB';
        $format = 'plain';
        $source = '{{var1}} test {{var2}}';

        $dataset1Name = 'Dataset 1';
        $dataset1Values = [
            'var1' => 'dataset1 value1',
            'var2' => 'dataset1 value2',
        ];
        $renderedDataset1Content = 'dataset1 value1 test dataset1 value2';
        $renderedDataset1Full = 'header dataset1 value1 test dataset1 value2 footer';

        $dataset2Name = 'Dataset 2';
        $dataset2Values = [
            'var1' => 'dataset2 value1',
            'var2' => 'dataset2 value2',
        ];

        $renderedDataset2Content = 'dataset2 value1 test dataset2 value2';
        $renderedDataset2Full = 'header dataset2 value1 test dataset2 value2 footer';

        $testData = [
            $dataset1Name => $dataset1Values,
            $dataset2Name => $dataset2Values
        ];

        $template = m::mock(Template::class);
        $template->shouldReceive('getDecodedTestData')
            ->andReturn($testData);
        $template->shouldReceive('getLocale')
            ->andReturn($locale);
        $template->shouldReceive('getFormat')
            ->andReturn($format);

        $query = PreviewTemplateSourceQry::create(
            [
                'id' => $templateId,
                'source' => $source,
            ]
        );

        $this->mockedSmServices['TemplateTwigRenderer']->shouldReceive('renderString')
            ->with($source, $dataset1Values)
            ->andReturn($renderedDataset1Content);

        $this->mockedSmServices['TemplateStrategySelectingViewRenderer']->shouldReceive('render')
            ->with($locale, $format, 'default', ['content' => $renderedDataset1Content])
            ->andReturn($renderedDataset1Full);

        $this->mockedSmServices['TemplateTwigRenderer']->shouldReceive('renderString')
            ->with($source, $dataset2Values)
            ->andReturn($renderedDataset2Content);

        $this->mockedSmServices['TemplateStrategySelectingViewRenderer']->shouldReceive('render')
            ->with($locale, $format, 'default', ['content' => $renderedDataset2Content])
            ->andReturn($renderedDataset2Full);

        $this->repoMap['Template']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($template);

        $expectedResponse = [
            $dataset1Name => $renderedDataset1Full,
            $dataset2Name => $renderedDataset2Full
        ];

        $result = $this->sut->handleQuery($query);

        $this->assertEquals($expectedResponse, $result);
    }

    public function testRenderingFailure(): void
    {
        $templateId = 6;
        $locale = 'en_GB';
        $format = 'plain';
        $source = '{{var1}} test {{var2}}';

        $dataset1Name = 'Dataset 1';

        $dataset1Values = [
            'var1' => 'dataset1 value1',
            'var2' => 'dataset1 value2',
        ];

        $testData = [
            'Dataset 1' => $dataset1Values,
            'Dataset 2' => [
                'var1' => 'dataset2 value1',
                'var2' => 'dataset2 value2',
            ]
        ];

        $template = m::mock(Template::class);
        $template->shouldReceive('getDecodedTestData')
            ->andReturn($testData);
        $template->shouldReceive('getLocale')
            ->andReturn($locale);
        $template->shouldReceive('getFormat')
            ->andReturn($format);

        $this->mockedSmServices['TemplateTwigRenderer']->shouldReceive('renderString')
            ->with($source, $dataset1Values)
            ->andThrow(new RuntimeException('Something went wrong'));

        $query = PreviewTemplateSourceQry::create(
            [
                'id' => $templateId,
                'source' => $source,
            ]
        );

        $this->repoMap['Template']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($template);

        $expectedResponse = [
            $dataset1Name => 'Something went wrong',
            'error' => true
        ];

        $result = $this->sut->handleQuery($query);

        $this->assertEquals($expectedResponse, $result);
    }
}
