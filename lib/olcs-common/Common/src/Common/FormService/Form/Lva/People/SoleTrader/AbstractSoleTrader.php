<?php

namespace Common\FormService\Form\Lva\People\SoleTrader;

use Common\FormService\Form\Lva\AbstractLvaFormService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Lva\PeopleLvaService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Sole Trader
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractSoleTrader extends AbstractLvaFormService
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService, protected PeopleLvaService $peopleLvaService)
    {
    }

    /**
     * Get sole trader form
     *
     * @param array $params Parameters for form
     *
     * @return \Common\Form\Form
     */
    public function getForm($params)
    {
        $form = $this->formHelper->createForm('Lva\SoleTrader');

        $this->alterForm($form, $params);

        return $form;
    }

    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form   Form
     * @param array           $params Parameters for form
     *
     * @return \Laminas\Form\Form
     */
    protected function alterForm($form, array $params)
    {
        // if not internal OR no  person OR already disqualified then hide the disqualify button
        if ($params['location'] !== 'internal' || empty($params['personId']) || $params['isDisqualified']) {
            $this->removeFormAction($form, 'disqualify');
        } else {
            $form->get('form-actions')->get('disqualify')->setValue($params['disqualifyUrl']);
        }

        if (isset($params['canModify']) && $params['canModify'] === false) {
            $this->peopleLvaService->lockPersonForm($form, $params['orgType']);
        }

        return $form;
    }
}
