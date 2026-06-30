<?php

namespace Common\View\Factory\Helper;

use Common\View\Helper\EscapeHtml;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class EscapeHtmlFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $htmlPurifier = $container->get('HtmlPurifier');
        return new EscapeHtml($htmlPurifier);
    }
}
