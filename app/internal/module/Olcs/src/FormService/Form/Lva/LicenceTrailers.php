<?php

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
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        parent::__construct($formHelper);
    }

    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form
     * @param TableBuilder $table
     * @return \Laminas\Form\Form
     */
    protected function alterForm($form, $table)
    {
        parent::alterForm($form, $table);

        $saveButton = $form->get('form-actions')->get('save');
        $this->formHelper->alterElementLabel($saveButton, 'internal.', FormHelperService::ALTER_LABEL_PREPEND);
        return $form;
    }
}
