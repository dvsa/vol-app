<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Template;

use Dvsa\Olcs\Api\Service\Template\PlainTextTwigRendererFactory;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Twig\Loader\ArrayLoader;

final class PlainTextTwigRendererFactoryTest extends TestCase
{
    private const string NAME = "Smith & Sons' <Haulage> Ltd";

    private function renderer(): TwigRenderer
    {
        $loader = new ArrayLoader(['en_GB/sms/letter-issued-sms' => 'Hey {{ name }}, today']);

        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willReturnMap([['TemplateDatabaseTwigLoader', $loader]]);

        return (new PlainTextTwigRendererFactory())($container, 'TemplatePlainTextTwigRenderer');
    }

    public function testRenderDoesNotHtmlEscape(): void
    {
        $this->assertSame(
            'Hey ' . self::NAME . ', today',
            $this->renderer()->render('en_GB/sms/letter-issued-sms', ['name' => self::NAME])
        );
    }

    public function testRenderStringDoesNotHtmlEscape(): void
    {
        $this->assertSame(
            'Hi ' . self::NAME,
            $this->renderer()->renderString('Hi {{ name }}', ['name' => self::NAME])
        );
    }

    public function testCompiledClassDiffersFromTheHtmlEnvironment(): void
    {
        // same name in both environments must never share a compiled (and cached) class
        $loader = new ArrayLoader(['t' => 'x']);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willReturnMap([['TemplateDatabaseTwigLoader', $loader]]);
        $plain = (new PlainTextTwigRendererFactory())($container, 'TemplatePlainTextTwigRenderer');
        $html = new \Twig\Environment($loader, ['strict_variables' => true]);

        $plainEnv = (new \ReflectionProperty(TwigRenderer::class, 'twig'))->getValue($plain);

        $this->assertNotSame($html->getTemplateClass('t'), $plainEnv->getTemplateClass('t'));
    }

    public function testStrictVariablesStillOn(): void
    {
        $this->expectException(\Twig\Error\RuntimeError::class);
        $this->renderer()->renderString('Hi {{ missing }}', []);
    }
}
