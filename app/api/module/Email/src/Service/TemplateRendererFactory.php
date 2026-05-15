<?php

namespace Dvsa\Olcs\Email\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class TemplateRenderer
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TemplateRendererFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TemplateRenderer
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TemplateRenderer
    {
        $templateRenderer = new TemplateRenderer();
        $templateRenderer->setViewRenderer($container->get('TemplateStrategySelectingViewRenderer'));

        // VOL-7238: derive Notify mode from the active mailer DSN (per-env via Parameter Store).
        // When the DSN scheme starts with `govuknotify`, sendEmailTemplate() in EmailAwareTrait
        // routes through renderMarkdownBody() and stamps the passthrough template UUID on the
        // outgoing message. Defaults to SMTP mode when no config is available.
        $config = $container->has('config') ? $container->get('config') : [];
        if (is_array($config)) {
            $dsn = $config['mail']['dsn'] ?? null;
            $templateRenderer->setNotifyMode(is_string($dsn) && str_starts_with($dsn, 'govuknotify'));

            $uuids = $config['email']['notify']['passthrough_templates'] ?? [];
            $templateRenderer->setPassthroughTemplateUuids(is_array($uuids) ? $uuids : []);
        }

        return $templateRenderer;
    }
}
