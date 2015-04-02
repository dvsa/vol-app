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
        $licenceId  = $this->getLicenceId();
        $form       = $this->getOverviewForm();
        $service    = $this->getServiceLocator()->get('Entity\Licence');
        $licence    = $service->getExtendedOverview($licenceId);
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $overviewHelper = $this->getServiceLocator()->get('Helper\LicenceOverview');

        $this->alterForm($form, $licence);

        if ($this->getRequest()->isPost()) {
            $data = (array) $this->getRequest()->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->save($data, $licence);
                $this->addSuccessMessage('Your changes have been saved');
                return $this->reload();
            }
        } else {
            // Prepare the form with editable data
            $form->setData($this->formatDataForForm($licence));
        }

        $isPsv = $licence['goodsOrPsv']['id'] == LicenceEntityService::LICENCE_CATEGORY_PSV;

        $isSpecialRestricted = $licence['licenceType']['id'] == LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED;

        $previousEntityData = $this->getPreviousEntityDataForLicence($licence);

        // Collate all the read-only data for the view
        $viewData = [
            'operatorName'              => $licence['organisation']['name'],
            'operatorId'                => $licence['organisation']['id'], // used for URL generation
            'numberOfLicences'          => count($licence['organisation']['licences']),
            'tradingName'               => $overviewHelper->getTradingNameFromLicence($licence),
            'currentApplications'       => $overviewHelper->getCurrentApplications($licence),
            'licenceNumber'             => $licence['licNo'],
            'licenceStartDate'          => $licence['inForceDate'],
            'licenceType'               => $service->getShortCodeForType($licence['licenceType']['id']),
            'licenceStatus'             => $translator->translate($licence['status']['id']),
            'surrenderedDate'           => $this->getSurrenderedDate($licence),
            'numberOfVehicles'          => $isSpecialRestricted ? null : count($licence['licenceVehicles']),
            'totalVehicleAuthorisation' => $isSpecialRestricted ? null : $licence['totAuthVehicles'],
            'numberOfOperatingCentres'  => $isSpecialRestricted ? null : count($licence['operatingCentres']),
            'totalTrailerAuthorisation' => $isPsv ? null : $licence['totAuthTrailers'],
            'numberOfIssuedDiscs'       => $isPsv && !$isSpecialRestricted ? count($licence['psvDiscs']) : null,
            'numberOfCommunityLicences' => $overviewHelper->getNumberOfCommunityLicences($licence),
            'openCases'                 => $overviewHelper->getOpenCases($licenceId),

            'isPsv' => $isPsv,

            // out of scope for OLCS-5209
            'currentReviewComplaints'    => $this->getReviewComplaintsCount($licence),
            'receivesMailElectronically' => null,
            'registeredForSelfService'   => null,

            'previousOperatorName' => $previousEntityData['operator'],
            'previousLicenceNumber' => $previousEntityData['licence']
        ];

        // Render the view
        $content = new ViewModel(
            array_merge(
                $viewData,
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

    /**
     * Get previous licence entity data.
     *
     * @param $licence The licence data.
     *
     * @return array The formatted data.
     */
    public function getPreviousEntityDataForLicence($licence)
    {
        $previousData = array(
            'operator' => null,
            'licence' => null
        );

        if (empty($licence['changeOfEntitys'])) {
            return $previousData;
        }

        $changeOfEntity = array_shift($licence['changeOfEntitys']);

        $previousData['operator'] = $changeOfEntity['oldOrganisationName'];
        $previousData['licence'] = $changeOfEntity['oldLicenceNo'];

        return $previousData;
    }

    public function getReviewComplaintsCount($licence)
    {
        $caseEntityService = $this->getServiceLocator()->get('Entity/Cases');
        $licenceCases = $caseEntityService->getComplaintsForLicence($licence['id']);

        $count = 0;
        foreach ($licenceCases as $licenceCase) {
            $count = $count + count($licenceCase['complaints']);
        }

        return $count;
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
        $form->get('details')->get('leadTcArea')->setValueOptions(
            $this->getServiceLocator()->get('Entity\TrafficArea')->getValueOptions()
        );

        $validStatuses = [
            LicenceEntityService::LICENCE_STATUS_VALID,
            LicenceEntityService::LICENCE_STATUS_SUSPENDED,
            LicenceEntityService::LICENCE_STATUS_CURTAILED,
        ];
        if (!in_array($licence['status']['id'], $validStatuses)) {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'details->reviewDate');
        }

        if (count($licence['organisation']['licences']) <= 1) {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'details->leadTcArea');
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
                'leadTcArea'       => $data['organisation']['leadTcArea']['id'],
            ],
            'id' => $data['id'],
            'version' => $data['version'],
        ];
    }

    /**
     * @param array $data data to save
     * @param array $licence data (need this to work out organisation id)
     * @todo move to Business Service?
     */
    protected function save($data, $licence)
    {
        $dateHelper = $this->getServiceLocator()->get('Helper\Date');

        $licenceSaveData = [];

        $licenceSaveData['expiryDate'] = $dateHelper->getDateObjectFromArray($data['details']['continuationDate'])
            ->format('Y-m-d');

        if (isset($data['details']['reviewDate'])) {
            $licenceSaveData['reviewDate'] = $dateHelper->getDateObjectFromArray($data['details']['reviewDate'])
                ->format('Y-m-d');
        }

        $this->getServiceLocator()->get('Entity\Licence')->forceUpdate($licence['id'], $licenceSaveData);

        if (isset($data['details']['leadTcArea'])) {
            $organisationSaveData = [
                'leadTcArea' => $data['details']['leadTcArea']
            ];
            $this->getServiceLocator()->get('Entity\Organisation')->forceUpdate(
                $licence['organisation']['id'],
                $organisationSaveData
            );
        }
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
