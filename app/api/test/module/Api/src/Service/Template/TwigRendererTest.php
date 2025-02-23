<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Template;

use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Twig\Environment;
use Twig\Template;
use Twig\TemplateWrapper;

class TwigRendererTest extends MockeryTestCase
{
    public function testRender(): void
    {
        $databaseTemplatePath = 'en_GB/plain/send-ecmt-successful';
        $variables = [
            'var1' => 'var1 value',
            'var2' => 'var2 value',
        ];

        $renderedTemplate = 'var1 value test var2 value';

        $environment = m::mock(Environment::class);
        $environment->expects('render')
            ->with($databaseTemplatePath, $variables)
            ->andReturn($renderedTemplate);

        $twigRenderer = new TwigRenderer($environment);

        $this->assertEquals(
            $renderedTemplate,
            $twigRenderer->render($databaseTemplatePath, $variables)
        );
    }

    public function testRenderString(): void
    {
        $templateString = '{{var1}} test {{var2}}';
        $variables = [
            'var1' => 'var1 value',
            'var2' => 'var2 value',
        ];

        $renderedTemplate = 'var1 value test var2 value';

        $environment = m::mock(Environment::class);
        $template = new TemplateWrapper($environment, m::mock(Template::class)); //this class is marked final

        $environment->expects('createTemplate')
            ->with($templateString)
            ->andReturn($template);
        $environment->expects('render')
            ->with($template, $variables)
            ->andReturn($renderedTemplate);

        $twigRenderer = new TwigRenderer($environment);

        $this->assertEquals(
            $renderedTemplate,
            $twigRenderer->renderString($templateString, $variables)
        );
    }
}
