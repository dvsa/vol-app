<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Template;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Twig\Environment;

/**
 * Renderer for plain text such as SMS, so "&" goes out as "&" and not "&amp;".
 * Same database templates as TemplateTwigRenderer, but with HTML autoescaping off.
 */
final class PlainTextTwigRendererFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): TwigRenderer
    {
        $twig = new Environment(
            $container->get('TemplateDatabaseTwigLoader'),
            [
                'strict_variables' => true,
                'autoescape' => false,
                // own compiled cache, so plain builds never land beside the HTML ones on disk
                'cache' => sys_get_temp_dir() . '/twig-plain',
                'auto_reload' => true,
            ]
        );
        // still needed: in one process the compiled class name ignores autoescape, and only this tells them apart
        $twig->addExtension(new PlainTextTwigExtension());

        return new TwigRenderer($twig);
    }
}
