<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractUndertakingsController as LvaAbstractUndertakingsController;
use Common\Service\Entity\LicenceEntityService as Licence;

/**
 * External Abstract Undertakings Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractUndertakingsController extends LvaAbstractUndertakingsController
{
    protected $location = 'external';

    /**
     * Index action
     *
     * @return \Common\View\Model\Section|\Zend\Http\Response
     */
    public function indexAction()
    {
        if ($this->isButtonPressed('change')) {
            return $this->goToOverview();
        }
        $this->getServiceLocator()->get('Script')->loadFile('undertakings');

        $response = parent::indexAction();

        if (
            $response instanceof \Zend\Http\PhpEnvironment\Response
            && ($this->isButtonPressed('submitAndPay') || $this->isButtonPressed('submit'))
        ) {
            // section completed
            $this->redirect()->toRoute(
                'lva-'.$this->lva . '/pay-and-submit',
                [$this->getIdentifierIndex() => $this->getIdentifier(), 'redirect-back' => 'undertakings'],
                [],
                true
            );
        }

        return $response;
    }

    /**
     * Format data for form
     *
     * @param array $applicationData application data
     *
     * @return array
     */
    protected function formatDataForForm($applicationData)
    {
        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];

        $output = array(
            'declarationsAndUndertakings' => array(
                'version' => $applicationData['version'],
                'id' => $applicationData['id'],
            )
        );

        if ($goodsOrPsv === Licence::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $interim = array();
            if (!is_null($applicationData['interimReason'])) {
                $interim['goodsApplicationInterim'] = "Y";
                $interim['goodsApplicationInterimReason'] = $applicationData['interimReason'];
            }

            $output['interim'] = $interim;
        }

        return $output;
    }

    /**
     * Update submit buttons
     *
     * @param \Common\Form\Form $form            form
     * @param array             $applicationData application data
     *
     * @return void
     */
    protected function updateSubmitButtons($form, $applicationData)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        if (!$this->isReadyToSubmit($applicationData)) {
            $formHelper->remove($form, 'form-actions->submitAndPay');
            $formHelper->remove($form, 'form-actions->submit');
            $formHelper->remove($form, 'form-actions->change');
            return;
        }

        if ($this->lva === 'application') {
            $formHelper->remove($form, 'form-actions->saveAndContinue');
        }
        $formHelper->remove($form, 'form-actions->save');
        $formHelper->remove($form, 'form-actions->cancel');

        if (floatval($applicationData['outstandingFeeTotal']) > 0) {
            $formHelper->remove($form, 'form-actions->submit');
        } else {
            $formHelper->remove($form, 'form-actions->submitAndPay');
        }

    }
}
