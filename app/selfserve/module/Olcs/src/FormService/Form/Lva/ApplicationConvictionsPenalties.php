<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\ConvictionsPenalties;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application convictions and penalties
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationConvictionsPenalties extends ConvictionsPenalties
{
    use ButtonsAlterations;

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
     * @param Form  $form   form
     * @param array $params parameters
     *
     * @return Form
     */
    #[\Override]
    protected function alterForm($form, array $params = [])
    {
        parent::alterForm($form, $params);
        $this->alterButtons($form);

        return $form;
    }
}
