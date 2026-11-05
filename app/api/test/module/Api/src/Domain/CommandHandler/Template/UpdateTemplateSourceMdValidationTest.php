<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Template;

use Dvsa\Olcs\Api\Domain\CommandHandler\Template\UpdateTemplateSource as Sut;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Template as TemplateRepo;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Transfer\Command\Template\UpdateTemplateSource as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Validator behaviour specifically for `format='md'` rows — the Markdown-Twig path for
 * GOV.UK Notify passthrough templates.
 */
final class UpdateTemplateSourceMdValidationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();

        $this->mockRepo('Template', TemplateRepo::class);

        $this->mockedSmServices = [
            'TemplateTwigRenderer' => m::mock(TwigRenderer::class),
            // md is checked as it is sent to Notify, without HTML escaping
            'TemplatePlainTextTwigRenderer' => m::mock(TwigRenderer::class),
        ];

        parent::setUp();
    }

    public function testAcceptsValidMarkdown(): void
    {
        $source = '# Hello {{ name }}\n\nThis is **safe** Markdown.';
        $command = Cmd::create(['id' => 1, 'source' => $source]);
        $dataset = ['name' => 'world'];

        $this->mockedSmServices['TemplatePlainTextTwigRenderer']->shouldReceive('renderString')
            ->with($source, $dataset)
            ->andReturn("# Hello world\n\nThis is **safe** Markdown.");

        $template = m::mock(Template::class);
        $template->shouldReceive('getDecodedTestData')->andReturn(['Default' => $dataset]);
        $template->shouldReceive('getFormat')->andReturn('md');
        $template->shouldReceive('setSource')->once()->with($source);

        $this->repoMap['Template']->shouldReceive('fetchUsingId')->andReturn($template);
        $this->repoMap['Template']->shouldReceive('save')->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Template source updated'], $result->getMessages());
    }

    public function testRejectsControlCharactersInMarkdown(): void
    {
        $source = 'Hello';
        $command = Cmd::create(['id' => 1, 'source' => $source]);
        $dataset = [];

        $this->mockedSmServices['TemplatePlainTextTwigRenderer']->shouldReceive('renderString')
            ->with($source, $dataset)
            ->andReturn("Hello\x00world");

        $template = m::mock(Template::class);
        $template->shouldReceive('getDecodedTestData')->andReturn(['Default' => $dataset]);
        $template->shouldReceive('getFormat')->andReturn('md');

        $this->repoMap['Template']->shouldReceive('fetchUsingId')->andReturn($template);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('control characters');

        $this->sut->handleCommand($command);
    }

    public function testSkipsMarkdownValidationForHtmlRows(): void
    {
        // An html-format template with <script> in its output must NOT be rejected by the md
        // validator — the existing HTML-escaping behaviour handles that concern.
        $source = 'ok';
        $command = Cmd::create(['id' => 1, 'source' => $source]);
        $dataset = [];

        // html stays on the escaping renderer
        $this->mockedSmServices['TemplateTwigRenderer']->shouldReceive('renderString')
            ->with($source, $dataset)
            ->andReturn('<script>alert(1)</script>');
        $this->mockedSmServices['TemplatePlainTextTwigRenderer']->shouldNotReceive('renderString');

        $template = m::mock(Template::class);
        $template->shouldReceive('getDecodedTestData')->andReturn(['Default' => $dataset]);
        $template->shouldReceive('getFormat')->andReturn('html');
        $template->shouldReceive('setSource')->once();

        $this->repoMap['Template']->shouldReceive('fetchUsingId')->andReturn($template);
        $this->repoMap['Template']->shouldReceive('save')->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Template source updated'], $result->getMessages());
    }

    public function testMarkdownIsCheckedWithoutHtmlEscaping(): void
    {
        $source = 'Hello {{ name }}';
        $command = Cmd::create(['id' => 1, 'source' => $source]);
        $dataset = ['name' => 'Smith & Sons'];

        $container = m::mock(\Psr\Container\ContainerInterface::class);
        $container->shouldReceive('get')->with('TemplateDatabaseTwigLoader')->andReturn(new \Twig\Loader\ArrayLoader([]));
        $real = (new \Dvsa\Olcs\Api\Service\Template\PlainTextTwigRendererFactory())($container, 'x');
        $rendered = null;
        $this->mockedSmServices['TemplatePlainTextTwigRenderer']->shouldReceive('renderString')
            ->once()
            ->andReturnUsing(function ($src, $vars) use ($real, &$rendered) {
                return $rendered = $real->renderString($src, $vars);
            });
        $this->mockedSmServices['TemplateTwigRenderer']->shouldNotReceive('renderString');

        $template = m::mock(Template::class);
        $template->shouldReceive('getDecodedTestData')->andReturn(['Default' => $dataset]);
        $template->shouldReceive('getFormat')->andReturn('md');
        $template->shouldReceive('setSource')->once()->with($source);

        $this->repoMap['Template']->shouldReceive('fetchUsingId')->andReturn($template);
        $this->repoMap['Template']->shouldReceive('save')->once();

        $this->sut->handleCommand($command);

        $this->assertSame('Hello Smith & Sons', $rendered);
    }
}
