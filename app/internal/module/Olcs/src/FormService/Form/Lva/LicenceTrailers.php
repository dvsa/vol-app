<?php

/**
 * Licence Trailers
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\CommonLicenceTrailers as CommonLicenceTrailers;
use Common\Service\Helper\FormHelperService;

/**
 * Licence Trailers
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
class LicenceTrailers extends CommonLicenceTrailers
{
    /**
     * Make form alterations
     *
     * @param \Zend\Form\Form $form
     * @param TableBuilder $table
     * @return \Zend\Form\Form
     */
    protected function alterForm($form, $table)
    {
        parent::alterForm($form, $table);

        $saveButton = $form->get('form-actions')->get('save');
        $this->getFormHelper()->alterElementLabel($saveButton, 'internal.', FormHelperService::ALTER_LABEL_PREPEND);
        return $form;
    }
}
