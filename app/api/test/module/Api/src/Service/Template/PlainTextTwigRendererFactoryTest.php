<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Template;

use Dvsa\Olcs\Api\Service\Template\PlainTextTwigRendererFactory;
use Dvsa\Olcs\Api\Service\Template\TwigEnvironmentFactory;
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

    public function testCompilesIntoItsOwnCacheDirectory(): void
    {
        $plainEnv = (new \ReflectionProperty(TwigRenderer::class, 'twig'))->getValue($this->renderer());

        $this->assertIsString($plainEnv->getCache());
        $this->assertNotSame(sys_get_temp_dir(), rtrim($plainEnv->getCache(), '/'));
        $this->assertStringStartsWith(sys_get_temp_dir(), $plainEnv->getCache());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('htmlFirst')]
    public function testBothEnvironmentsKeepTheirEscapingInOneProcess(bool $htmlFirst): void
    {
        // a fresh name per run, so nothing compiled by an earlier run is reused
        $name = 'en_GB/sms/both-' . bin2hex(random_bytes(6));
        $string = 'String {{ name }} ' . bin2hex(random_bytes(6));
        $loader = new ArrayLoader([$name => 'Hey {{ name }}']);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willReturnMap([['TemplateDatabaseTwigLoader', $loader]]);

        $html = new TwigRenderer((new TwigEnvironmentFactory())($container, 'TemplateTwigEnvironment'));
        $plain = (new PlainTextTwigRendererFactory())($container, 'TemplatePlainTextTwigRenderer');
        $vars = ['name' => self::NAME];
        $escaped = htmlspecialchars(self::NAME, ENT_QUOTES);

        $order = $htmlFirst ? [$html, $plain] : [$plain, $html];
        foreach ($order as $renderer) {
            $renderer->render($name, $vars);
            $renderer->renderString($string, $vars);
        }

        $this->assertSame('Hey ' . $escaped, $html->render($name, $vars));
        $this->assertSame('Hey ' . self::NAME, $plain->render($name, $vars));
        $this->assertStringStartsWith('String ' . $escaped . ' ', $html->renderString($string, $vars));
        $this->assertStringStartsWith('String ' . self::NAME . ' ', $plain->renderString($string, $vars));
    }

    public static function htmlFirst(): array
    {
        return ['html first' => [true], 'plain first' => [false]];
    }

    public function testStrictVariablesStillOn(): void
    {
        $this->expectException(\Twig\Error\RuntimeError::class);
        $this->renderer()->renderString('Hi {{ missing }}', []);
    }
}
