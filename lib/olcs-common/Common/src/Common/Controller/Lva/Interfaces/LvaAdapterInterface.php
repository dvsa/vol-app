<?php

/**
 * Lva Adapter Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller\Lva\Interfaces;

use Laminas\Form\Form;

/**
 * Lva Adapter Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface LvaAdapterInterface
{
    public function getIdentifier();

    /**
     * Alter the form based on the LVA rules
     */
    public function alterForm(Form $form);
}
