<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Template;

use Dvsa\Olcs\Api\Service\Template\StrategySelectingViewRenderer;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Twig\Loader\LoaderInterface as TwigLoader;

/**
 * StrategySelectingViewRendererTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class StrategySelectingViewRendererTest extends MockeryTestCase
{
    public function testUseTwigRendererWhenTemplateIsEditable(): void
    {
        $locale = 'en_GB';
        $format = 'plain';
        $template = 'send-ecmt-successful';
        $templatePath = 'en_GB/plain/send-ecmt-successful';
        $variables = [
            'variable1' => 'value1',
            'variable2' => 'value2',
        ];
        $renderedTwigTemplate = 'value1 test value2';

        $legacyViewRenderer = m::mock(RendererInterface::class);

        $twigRenderer = m::mock(TwigRenderer::class);
        $twigRenderer->shouldReceive('render')
            ->with($templatePath, $variables)
            ->once()
            ->andReturn($renderedTwigTemplate);

        $twigLoader = m::mock(TwigLoader::class);
        $twigLoader->shouldReceive('exists')
            ->with($templatePath)
            ->andReturn(true);

        $sut = new StrategySelectingViewRenderer(
            $legacyViewRenderer,
            $twigRenderer,
            $twigLoader
        );

        $this->assertEquals(
            $renderedTwigTemplate,
            $sut->render($locale, $format, $template, $variables)
        );
    }

    public function testUseLegacyRendererWhenTemplateIsNotEditable(): void
    {
        $locale = 'cy_GB';
        $format = 'html';
        $template = 'send-ecmt-successful';
        $variables = [
            'variable1' => 'value1',
            'variable2' => 'value2',
        ];

        $templatePath = 'cy_GB/html/send-ecmt-successful';
        $renderedLegacyTemplate = '<p>value1 test value2</p>';

        $legacyViewRenderer = m::mock(RendererInterface::class);
        $legacyViewRenderer->shouldReceive('render')
            ->with(m::type(ViewModel::class))
            ->andReturnUsing(function ($viewModel) use ($templatePath, $variables, $renderedLegacyTemplate) {
                $this->assertEquals($templatePath, $viewModel->getTemplate());
                $this->assertEquals($variables, $viewModel->getVariables()->getArrayCopy());

                return $renderedLegacyTemplate;
            });

        $twigRenderer = m::mock(TwigRenderer::class);

        $twigLoader = m::mock(TwigLoader::class);
        $twigLoader->shouldReceive('exists')
            ->with($templatePath)
            ->andReturn(false);

        $sut = new StrategySelectingViewRenderer(
            $legacyViewRenderer,
            $twigRenderer,
            $twigLoader
        );

        $this->assertEquals(
            $renderedLegacyTemplate,
            $sut->render($locale, $format, $template, $variables)
        );
    }

    public function testMarkdownUsesThePlainTextRenderer(): void
    {
        $twigRenderer = m::mock(TwigRenderer::class);
        $twigRenderer->shouldNotReceive('render');
        $plainRenderer = m::mock(TwigRenderer::class);
        $plainRenderer->shouldReceive('render')->once()
            ->with('en_GB/md/my-email', ['name' => 'x'])
            ->andReturn('Hi x');

        $twigLoader = m::mock(TwigLoader::class);
        $twigLoader->shouldReceive('exists')->with('en_GB/md/my-email')->andReturn(true);

        $sut = new StrategySelectingViewRenderer(
            m::mock(RendererInterface::class),
            $twigRenderer,
            $twigLoader,
            $plainRenderer
        );

        $this->assertSame('Hi x', $sut->render('en_GB', 'md', 'my-email', ['name' => 'x']));
    }

    public function testNotifyMarkdownIsNotHtmlEscapedButHtmlStillIs(): void
    {
        $loader = new \Twig\Loader\ArrayLoader([
            'en_GB/md/my-email' => 'Hi {{ name }}',
            'en_GB/html/my-email' => '<p>Hi {{ name }}</p>',
        ]);
        $container = m::mock(\Psr\Container\ContainerInterface::class);
        $container->shouldReceive('get')->with('TemplateDatabaseTwigLoader')->andReturn($loader);
        $plainRenderer = (new \Dvsa\Olcs\Api\Service\Template\PlainTextTwigRendererFactory())($container, 'x');
        $twigRenderer = new TwigRenderer(new \Twig\Environment($loader, ['strict_variables' => true]));

        $sut = new StrategySelectingViewRenderer(m::mock(RendererInterface::class), $twigRenderer, $loader, $plainRenderer);
        $vars = ['name' => 'Smith & Sons <b>'];

        $markdown = $sut->render('en_GB', 'md', 'my-email', $vars);
        // Notify escapes HTML itself, so the markdown it gets must carry the raw text.
        $this->assertSame('Hi Smith & Sons <b>', $markdown);
        $this->assertSame('<p>Hi Smith &amp; Sons &lt;b&gt;</p>', $sut->render('en_GB', 'html', 'my-email', $vars));

        // The local preview of that markdown escapes it rather than letting the tag through.
        $html = \Dvsa\Olcs\Email\View\NotifyChrome::renderMarkdownBody(
            $markdown,
            \Dvsa\Olcs\Email\View\NotifyChrome::markdownConverter()
        );
        $this->assertStringContainsString('Hi Smith &amp; Sons &lt;b&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
