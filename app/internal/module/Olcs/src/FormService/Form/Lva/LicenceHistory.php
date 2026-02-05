<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\LicenceHistory as CommonLicenceHistory;
use Common\Service\Helper\FormHelperService;

/**
 * LicenceHistory Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class LicenceHistory extends CommonLicenceHistory
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
     * @return \Laminas\Form\Form
     */
    #[\Override]
    protected function alterForm($form)
    {
        parent::alterForm($form);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
