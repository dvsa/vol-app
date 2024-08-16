<?php

namespace Dvsa\OlcsTest\Api\Service\Template;

use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Twig\Environment;

/**
 * TwigRendererTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class TwigRendererTest extends MockeryTestCase
{
    public function testRender()
    {
        $databaseTemplatePath = 'en_GB/plain/send-ecmt-successful';
        $variables = [
            'var1' => 'var1 value',
            'var2' => 'var2 value',
        ];

        $renderedTemplate = 'var1 value test var2 value';

        $environment = m::mock(Environment::class);
        $environment->shouldReceive('render')
            ->with($databaseTemplatePath, $variables)
            ->andReturn($renderedTemplate);

        $twigRenderer = new TwigRenderer($environment);

        $this->assertEquals(
            $renderedTemplate,
            $twigRenderer->render($databaseTemplatePath, $variables)
        );
    }

    public function testRenderString()
    {
        $templateString = '{{var1}} test {{var2}}';
        $variables = [
            'var1' => 'var1 value',
            'var2' => 'var2 value',
        ];

        $renderedTemplate = 'var1 value test var2 value';

        $template = m::mock();
        $template->shouldReceive('render')
            ->with($variables)
            ->andReturn($renderedTemplate);

        $environment = m::mock(Environment::class);
        $environment->shouldReceive('createTemplate')
            ->with($templateString)
            ->andReturn($template);

        $twigRenderer = new TwigRenderer($environment);

        $this->assertEquals(
            $renderedTemplate,
            $twigRenderer->renderString($templateString, $variables)
        );
    }
}
