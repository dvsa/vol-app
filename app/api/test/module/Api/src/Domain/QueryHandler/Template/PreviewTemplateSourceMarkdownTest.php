<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\Template\PreviewTemplateSource;
use Dvsa\Olcs\Api\Domain\Repository\Template as TemplateRepo;
use Dvsa\Olcs\Api\Entity\Template\Template;
use Dvsa\Olcs\Api\Service\Template\StrategySelectingViewRenderer;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Transfer\Query\Template\PreviewTemplateSource as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class PreviewTemplateSourceMarkdownTest extends QueryHandlerTestCase
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

    public function testMdFormatRendersHtmlPreviewBypassingViewRenderer(): void
    {
        $source = 'Hello **{{ name }}**';
        $query = Qry::create(['id' => 1, 'source' => $source]);

        $template = m::mock(Template::class);
        $template->shouldReceive('getDecodedTestData')->andReturn(['Default' => ['name' => 'world']]);
        $template->shouldReceive('getLocale')->andReturn('en_GB');
        $template->shouldReceive('getFormat')->andReturn(PreviewTemplateSource::FORMAT_MARKDOWN);
        $template->shouldReceive('getDescription')->andReturn('Greeting template');

        $this->mockedSmServices['TemplateTwigRenderer']
            ->shouldReceive('renderString')->with($source, ['name' => 'world'])
            ->andReturn('Hello **world**');

        // Bypass the strategy-selecting renderer entirely for md rows.
        $this->mockedSmServices['TemplateStrategySelectingViewRenderer']->shouldNotReceive('render');

        $this->repoMap['Template']->shouldReceive('fetchUsingId')->with($query)->andReturn($template);

        $result = $this->sut->handleQuery($query);

        $this->assertArrayHasKey('Default', $result);
        $this->assertStringContainsString('<strong>world</strong>', $result['Default']);
        // New shared chrome wrap: GOV.UK header + description used as subject heading.
        $this->assertStringContainsString('GOV.UK', $result['Default']);
        $this->assertStringContainsString('Greeting template', $result['Default']);
    }
}
