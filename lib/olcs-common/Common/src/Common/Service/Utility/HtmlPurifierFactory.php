<?php

namespace Common\Service\Utility;

use HTMLPurifier;
use HTMLPurifier_Config;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Html Purifier Factory
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class HtmlPurifierFactory implements FactoryInterface
{
    protected $whiteList =
        'a[href|class|id|target],p[class|style|id],b,i[class|style|id],strong,br,span[class|style|id],h1[class|id],h2[class|id],h3[class|id],h4[class|id],li[class|style|id],ul[class|style|id]';

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): HTMLPurifier
    {
        $appConfig = $container->get('Config');
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $appConfig['html-purifier-cache-dir']);
        $config->set('HTML.Allowed', $this->whiteList);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        return new HTMLPurifier($config);
    }
}
