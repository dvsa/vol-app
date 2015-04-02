<?php

namespace Olcs\Controller\Lva\Traits;

use Common\Form\Elements\InputFilters\SelectEmpty as SelectElement;
use Zend\View\Model\ViewModel;
use Common\Service\Entity\LicenceEntityService as Licence;

/**
 * This trait enables the Application and Variation overview controllers to
 * share identical behaviour
 */
trait ApplicationOverviewTrait
{
    /**
     * Application overview
     */
    public function indexAction()
    {
        $id = $this->getIdentifier();

        $application = $this->getServiceLocator()->get('Entity\Application')->getOverview($id);

        $licence = $this->getServiceLocator()->get('Entity\Licence')
            ->getExtendedOverview($application['licence']['id']);

        $form = $this->getOverviewForm();
        $this->alterForm($form);

        if ($this->getRequest()->isPost()) {
            $data = (array) $this->getRequest()->getPost();

            if ($this->isButtonPressed('cancel')) {
                $this->addSuccessMessage('flash-discarded-changes');
                return $this->reload();
            }

            $form->setData($data);
            if ($form->isValid()) {

                $this->save($form->getData());

                $this->addSuccessMessage('application.overview.saved');

                if ($this->isButtonPressed('saveAndContinue')) {
                     return $this->redirect()
                        ->toRoute(
                            'lva-'.$this->lva.'/type_of_licence',
                            [$this->getIdentifierIndex() => $this->getIdentifier()]
                        );
                }

                return $this->reload();
            }

        } else {
            $tracking = $this->getTrackingDataForApplication($application['id']);
            $formData = $this->formatDataForForm($application, $tracking);
            $form->setData($formData);
        }

        // Render the view
        $content = new ViewModel(
            array_merge(
                $this->getOverviewData($application, $licence),
                ['form' => $form]
            )
        );
        $content->setTemplate('pages/application/overview');

        return $this->render($content);
    }

    protected function getOverviewForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('ApplicationOverview');
    }

    /**
     * @param array $application application overview data
     * @param array $tracking tracking status data
     * @return array
     */
    protected function formatDataForForm($application, $tracking)
    {
        return [
            'details' => [
                'receivedDate'         => $application['receivedDate'],
                'targetCompletionDate' => $application['targetCompletionDate'],
                'leadTcArea'           => $application['licence']['organisation']['leadTcArea']['id'],
                'version'              => $application['version'],
                'id'                   => $application['id'],

            ],
            'tracking' => $tracking,
        ];
    }

    /**
     * @param int $applicationId
     * @return array
     */
    protected function getTrackingDataForApplication($applicationId)
    {
        return $this->getServiceLocator()->get('Entity\ApplicationTracking')
            ->getTrackingStatuses($applicationId);
    }

    protected function alterForm($form)
    {
        $form->get('details')->get('leadTcArea')->setValueOptions(
            $this->getServiceLocator()->get('Entity\TrafficArea')->getValueOptions()
        );

        $fieldset = $form->get('tracking');

        $stringHelper = $this->getServiceLocator()->get('Helper\String');

        // build up the tracking fieldset dynamically, based on relevant sections
        $sections = $this->getAccessibleSections();
        $options  = $this->getServiceLocator()->get('Entity\ApplicationTracking')->getValueOptions();
        foreach ($sections as $section) {
            $selectProperty = lcfirst($stringHelper->underscoreToCamel($section)) . 'Status';
            $select = new SelectElement($selectProperty);
            $select->setValueOptions($options);
            $select->setLabel('section.name.'.$section);
            $fieldset->add($select);
        }

        // modify label (it should be 'Save' not 'Save & return' as per AC)
        $form->get('form-actions')->get('save')->setLabel('Save');

        return $form;
    }

    /**
     * Save tracking data
     *
     * @param array $data
     * @todo move this to a Business Service
     */
    protected function save($data)
    {
        $trackingData = $data['tracking'];
        $applicationData = $data['details'];

        $this->getServiceLocator()->get('Entity\ApplicationTracking')
            ->save($trackingData);

        $this->getServiceLocator()->get('Entity\Application')
            ->save($applicationData);
    }

    /**
     * @param array $application application overview data
     * @param array $licence licence overview data
     * @return array multiItems for the readonly part of the view
     */
    protected function getOverviewData($application, $licence)
    {
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $overviewHelper = $this->getServiceLocator()->get('Helper\LicenceOverview');

        $isPsv = $application['goodsOrPsv']['id'] == Licence::LICENCE_CATEGORY_PSV;
        $isSpecialRestricted = $application['licenceType']['id'] == Licence::LICENCE_TYPE_SPECIAL_RESTRICTED;

        $overviewData = [
            'operatorName'              => $licence['organisation']['name'],
            'operatorId'                => $licence['organisation']['id'], // used for URL generation
            'numberOfLicences'          => count($licence['organisation']['licences']),
            'tradingName'               => $overviewHelper->getTradingNameFromLicence($licence),
            'currentApplications'       => $overviewHelper->getCurrentApplications($licence),
            'applicationCreated'        => $application['createdOn'],
            'oppositionCount'           => $this->getOppositionCount($application['id']),
            'licenceStatus'             => $translator->translate($licence['status']['id']),
            'interimStatus'             => $isPsv ? null :$this->getInterimStatus($application['id']),
            'outstandingFees'           => $this->getOutstandingFeeCount($application['id']),
            'licenceStartDate'          => $licence['inForceDate'],
            'continuationDate'          => $licence['expiryDate'],
            'numberOfVehicles'          => $isSpecialRestricted ? null : count($licence['licenceVehicles']),
            'totalVehicleAuthorisation' => $this->getTotalVehicleAuthorisation($application, $licence),
            'numberOfOperatingCentres'  => $isSpecialRestricted ? null : count($licence['operatingCentres']),
            'totalTrailerAuthorisation' => $this->getTotalTrailerAuthorisation($application, $licence),
            'numberOfIssuedDiscs'       => $isPsv && !$isSpecialRestricted ? count($licence['psvDiscs']) : null,
            'numberOfCommunityLicences' => $overviewHelper->getNumberOfCommunityLicences($licence),
            'openCases'                 => $overviewHelper->getOpenCases($licence['id']),

            'currentReviewComplaints'   => null, // @todo pending OLCS-7581
            'previousOperatorName'      => null, // @todo pending OLCS-8383
            'previousLicenceNumber'     => null, // @todo pending OLCS-8383

            // out of scope for OLCS-6831
            'outOfOpposition'            => null,
            'outOfRepresentation'        => null,
            'changeOfEntity'             => null,
            'receivesMailElectronically' => null,
            'registeredForSelfService'   => null,
        ];

        return $overviewData;
    }

    protected function getInterimStatus($id)
    {
        $applicationData = $this->getServiceLocator()->get('Entity\Application')
            ->getDataForInterim($id);

        $url = $this->getServiceLocator()->get('Helper\Url')
            ->fromRoute('lva-'.$this->lva.'/interim', [], [], true);

        if (
            isset($applicationData['interimStatus']['id'])
            && !empty($applicationData['interimStatus']['id'])
        ) {
            $interimStatus = sprintf(
                '%s (<a href="%s">Interim details</a>)',
                $applicationData['interimStatus']['description'],
                $url
            );
        } else {
            $interimStatus = sprintf('None (<a href="%s">add interim</a>)', $url);
        }

        return $interimStatus;
    }

    protected function getOutstandingFeeCount($applicationId)
    {
        $fees = $this->getServiceLocator()->get('Entity\Fee')
            ->getOutstandingFeesForApplication($applicationId);

        return count($fees);
    }

    protected function getOppositionCount($applicationId)
    {
        $oppositions = $this->getServiceLocator()->get('Entity\Opposition')
            ->getForApplication($applicationId);

        return count($oppositions);
    }

    protected function getTotalVehicleAuthorisation($application, $licence)
    {
        if ($application['licenceType']['id'] == Licence::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            return null;
        }

        $str = $licence['totAuthVehicles'];

        if ($application['totAuthVehicles'] != $licence['totAuthVehicles']) {
            $str .= ' (' . $application['totAuthVehicles'] . ')';
        }

        return $str;
    }

    protected function getTotalTrailerAuthorisation($application, $licence)
    {
        if ($application['goodsOrPsv']['id'] == Licence::LICENCE_CATEGORY_PSV) {
            return null;
        }

        $str = $licence['totAuthTrailers'];

        if ($application['totAuthTrailers'] != $licence['totAuthTrailers']) {
            $str .= ' (' . $application['totAuthTrailers'] . ')';
        }

        return $str;
    }
}
