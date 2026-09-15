<?php

namespace Common\Controller\Lva\Adapters;

use Psr\Container\ContainerInterface;
use Laminas\Form\Form;

class LicenceLvaAdapter extends AbstractLvaAdapter
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * @return void
     */
    #[\Override]
    public function getIdentifier()
    {
    }

    /**
     * Alter the form based on the LVA rules
     */
    #[\Override]
    public function alterForm(Form $form): void
    {
        $form->get('form-actions')->remove('saveAndContinue');
    }
}
