<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\ConvictionsPenalties as CommonConvictionsPenalties;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;

/**
 * ConvictionsPenalties Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ConvictionsPenalties extends CommonConvictionsPenalties
{
    protected TranslationHelperService $translator;
    protected UrlHelperService $urlHelper;
    protected FormHelperService $formHelper;

    public function __construct(
        FormHelperService $formHelper,
        TranslationHelperService $translator,
        UrlHelperService $urlHelper
    ) {
        parent::__construct($formHelper, $translator, $urlHelper);
    }

    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form   form
     * @param array           $params params
     *
     * @return \Laminas\Form\Form
     */
    #[\Override]
    protected function alterForm($form, array $params)
    {
        parent::alterForm($form, $params);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
