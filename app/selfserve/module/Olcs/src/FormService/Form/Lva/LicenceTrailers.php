<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\CommonLicenceTrailers as CommonLicenceTrailers;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use Common\Service\Table\TableBuilder;

/**
 * Licence Trailers
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
class LicenceTrailers extends CommonLicenceTrailers
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    /**
     * Alter form
     *
     * @param Form         $form  form
     * @param TableBuilder $table table
     *
     * @return Form
     */
    #[\Override]
    protected function alterForm($form, $table)
    {
        parent::alterForm($form, $table);
        $this->formHelper->remove($form, 'form-actions->cancel');

        return $form;
    }
}
