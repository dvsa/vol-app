<?php

namespace Common\Controller\Lva\Adapters;

use Common\Controller\Lva\Interfaces\LvaAdapterInterface;
use Psr\Container\ContainerInterface;
use Laminas\Form\Form;

abstract class AbstractLvaAdapter extends AbstractControllerAwareAdapter implements LvaAdapterInterface
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * Alter the form based on the LVA rules
     *
     * @return void
     */
    #[\Override]
    public function alterForm(Form $form)
    {
    }
}
