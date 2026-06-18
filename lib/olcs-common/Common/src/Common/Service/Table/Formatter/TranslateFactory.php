<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;

class TranslateFactory
{
    public function __invoke(ContainerInterface $container)
    {

        $translator = $container->get('translator');
        $dataHelper = $container->get('Helper\Data');
        return new Translate($translator, $dataHelper);
    }
}
