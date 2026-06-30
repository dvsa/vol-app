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
class UpdateTemplateSourceMdValidationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();

        $this->mockRepo('Template', TemplateRepo::class);

        $this->mockedSmServices = [
            'TemplateTwigRenderer' => m::mock(TwigRenderer::class),
        ];

        parent::setUp();
    }

    public function testAcceptsValidMarkdown(): void
    {
        $source = '# Hello {{ name }}\n\nThis is **safe** Markdown.';
        $command = Cmd::create(['id' => 1, 'source' => $source]);
        $dataset = ['name' => 'world'];

        $this->mockedSmServices['TemplateTwigRenderer']->shouldReceive('renderString')
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

        $this->mockedSmServices['TemplateTwigRenderer']->shouldReceive('renderString')
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

        $this->mockedSmServices['TemplateTwigRenderer']->shouldReceive('renderString')
            ->with($source, $dataset)
            ->andReturn('<script>alert(1)</script>');

        $template = m::mock(Template::class);
        $template->shouldReceive('getDecodedTestData')->andReturn(['Default' => $dataset]);
        $template->shouldReceive('getFormat')->andReturn('html');
        $template->shouldReceive('setSource')->once();

        $this->repoMap['Template']->shouldReceive('fetchUsingId')->andReturn($template);
        $this->repoMap['Template']->shouldReceive('save')->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Template source updated'], $result->getMessages());
    }
}
