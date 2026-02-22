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
class StrategySelectingViewRendererTest extends MockeryTestCase
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
}
