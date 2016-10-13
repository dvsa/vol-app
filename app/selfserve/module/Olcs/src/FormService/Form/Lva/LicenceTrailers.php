<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\CommonLicenceTrailers as CommonLicenceTrailers;
use Zend\Form\Form;
use Common\Service\Table\TableBuilder;

/**
 * Licence Trailers
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
class LicenceTrailers extends CommonLicenceTrailers
{
    /**
     * Alter form
     *
     * @param Form         $form  form
     * @param TableBuilder $table table
     *
     * @return Form
     */
    protected function alterForm($form, $table)
    {
        parent::alterForm($form, $table);
        $this->getFormHelper()->remove($form, 'form-actions->cancel');

        return $form;
    }
}
