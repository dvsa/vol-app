<?php

/**
 * Internal Licence Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Zend\View\Model\ViewModel;
use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Entity\ApplicationEntityService;

/**
 * Internal Licence Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractController implements LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';

    /**
     * Licence overview
     */
    public function indexAction()
    {
        $licenceId = $this->getLicenceId();
        $form      = $this->getOverviewForm();
        $licence   = $this->getOverviewData($licenceId);

        $this->alterForm($form, $licence);

        if ($this->getRequest()->isPost()) {
            $data = (array) $this->getRequest()->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $response = $this->getServiceLocator()->get('BusinessServiceManager')
                    ->get('Lva\LicenceOverview')
                    ->process($data);
                if ($response->isOk()) {
                    $this->addSuccessMessage('licence.overview.saved');
                    return $this->reload();
                } else {
                    $this->addErrorMessage('licence.overview.save.failed');
                }
            }
        } else {
            // Prepare the form with editable data
            $form->setData($this->formatDataForForm($licence));
        }

        // Render the view
        $content = new ViewModel(
            array_merge(
                $this->getServiceLocator()->get('Helper\LicenceOverview')->getViewData($licence),
                ['form' => $form]
            )
        );
        $content->setTemplate('pages/licence/overview');

        return $this->render($content);
    }

    public function createVariationAction()
    {
        $varId = $this->getServiceLocator()->get('Entity\Application')
            ->createVariation($this->getIdentifier());

        return $this->redirect()->toRouteAjax('lva-variation', ['application' => $varId]);
    }

    protected function getOverviewData($licenceId)
    {
        return $this->getServiceLocator()->get('Entity\Licence')->getExtendedOverview($licenceId);
    }

    /**
     * @return Common\Form\Form
     */
    protected function getOverviewForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('LicenceOverview');
    }

    /**
     * @param Common\Form\Form $form
     * @return Common\Form\Form
     */
    protected function alterForm($form, $licence)
    {
        $validStatuses = [
            LicenceEntityService::LICENCE_STATUS_VALID,
            LicenceEntityService::LICENCE_STATUS_SUSPENDED,
            LicenceEntityService::LICENCE_STATUS_CURTAILED,
        ];
        if (!in_array($licence['status']['id'], $validStatuses)) {
            // remove review date field if licence is not active
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'details->reviewDate');
        }

        if (count($licence['organisation']['licences']) <= 1) {
            // remove TC Area dropdown if there are no active licences
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'details->leadTcArea');
        } else {
            $form->get('details')->get('leadTcArea')->setValueOptions(
                $this->getServiceLocator()->get('Entity\TrafficArea')->getValueOptions()
            );
        }

        if ((boolean)$licence['organisation']['leadTcArea']['isWales'] !== true) {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'details->welshLanguage');
        }

        return $form;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function formatDataForForm($data)
    {
        return [
            'details' => [
                'continuationDate' => $data['expiryDate'],
                'reviewDate'       => $data['reviewDate'],
                'translateToWelsh' => $data['translateToWelsh'],
                'leadTcArea'       => $data['organisation']['leadTcArea']['id'],
            ],
            'id' => $data['id'],
            'version' => $data['version'],
        ];
    }

    public function printAction()
    {
        $licenceId  = $this->getLicenceId();

        $this->getServiceLocator()
            ->get('Processing\Licence')
            ->generateDocument($licenceId);

        $this->addSuccessMessage('licence.print.success');

        return $this->redirect()->toRoute(
            'lva-licence/overview',
            [],
            [],
            true
        );
    }
}
