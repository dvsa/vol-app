<?php

namespace Common\Controller\Lva\Adapters;

use Common\Controller\Lva\Interfaces\BusinessTypeAdapterInterface;
use Psr\Container\ContainerInterface;
use Laminas\Form\Form;

class GenericBusinessTypeAdapter extends AbstractAdapter implements BusinessTypeAdapterInterface
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    #[\Override]
    public function alterFormForOrganisation(Form $form, $orgId): void
    {
        // no-op
    }
}
