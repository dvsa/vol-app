<?php

/**
 * Internal Licence Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Licence\PrintLicence;
use Dvsa\Olcs\Transfer\Query\Licence\Overview as LicenceQry;
use Dvsa\Olcs\Transfer\Command\Licence\Overview as OverviewCmd;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Zend\View\Model\ViewModel;

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
     *
     * @return array|Zend/Http/Response
     */
    public function indexAction()
    {
        $licenceId = $this->getLicenceId();
        $form      = $this->getOverviewForm();
        $licence   = $this->getOverviewData($licenceId);

        // if unlicensed, redirect to unlicensed operator page
        if ($licence['status']['id'] == RefData::LICENCE_STATUS_UNLICENSED) {
            return $this->redirect()->toRoute(
                'operator-unlicensed',
                ['organisation' => $licence['organisation']['id']]
            );
        }
        $statusesForRedirect = [
            RefData::LICENCE_STATUS_NOT_SUBMITTED,
            RefData::LICENCE_STATUS_CONSIDERATION,
            RefData::LICENCE_STATUS_GRANTED,
            RefData::LICENCE_STATUS_NOT_TAKEN_UP,
            RefData::LICENCE_STATUS_WITHDRAWN,
            RefData::LICENCE_STATUS_REFUSED
        ];
        if (in_array($licence['status']['id'], $statusesForRedirect)) {
            if ($licence['firstApplicationId']) {
                return $this->redirect()->toRoute(
                    'lva-application',
                    ['application' => $licence['firstApplicationId']]
                );
            }
        }

        $this->alterForm($form, $licence);

        if ($this->getRequest()->isPost()) {
            $data = (array) $this->getRequest()->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $dtoData = $this->mapData($form->getData());
                $cmd = OverviewCmd::create($dtoData);
                $response = $this->handleCommand($cmd);
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
                [
                    'form' => $form,
                    'title' => 'Overview'
                ]
            )
        );
        $content->setTemplate('sections/licence/pages/overview');

        return $this->render($content);
    }

    /**
     * get method OverviewData
     *
     * @param int $licenceId licenceID
     *
     * @return array|Zend/Http/Response
     */
    protected function getOverviewData($licenceId)
    {
        $query = LicenceQry::create(['id' => $licenceId]);
        $response = $this->handleQuery($query);
        return $response->getResult();
    }

    /**
     * get method overview form
     *
     * @return Common\Form\Form
     */
    protected function getOverviewForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('LicenceOverview');
    }

    /**
     * Form presentation logic
     *
     * @param Common\Form\Form $form    form
     * @param array            $licence licence
     *
     * @return Common\Form\Form
     */
    protected function alterForm($form, $licence)
    {
        $validStatuses = [
            RefData::LICENCE_STATUS_VALID,
            RefData::LICENCE_STATUS_SUSPENDED,
            RefData::LICENCE_STATUS_CURTAILED,
        ];
        if (!in_array($licence['status']['id'], $validStatuses)) {
            // remove review date field if licence is not active
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'details->reviewDate');
        }

        $form->get('details')->get('leadTcArea')->setValueOptions(
            $licence['valueOptions']['trafficAreas']
        );

        if ($licence['trafficArea']['isWales'] !== true) {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'details->translateToWelsh');
        }

        return $form;
    }

    /**
     * formate data form
     *
     * @param array $data data
     *
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

    /**
     * print action
     *
     * @return \Zend\Http\Response
     */
    public function printAction()
    {
        $response = $this->handleCommand(PrintLicence::create(['id' => $this->getLicenceId()]));

        if ($response->isOk()) {
            $this->addSuccessMessage('licence.print.success');
        } else {
            $this->addErrorMessage('licence.print.failed');
        }

        return $this->redirect()->toRoute('lva-licence/overview', [], [], true);
    }

    /**
     * mapData
     *
     * @param array $formData formData
     *
     * @return array
     */
    protected function mapData($formData)
    {
        $data = [
            'id' => $formData['id'],
            'version' => $formData['version'],
            'leadTcArea' => $formData['details']['leadTcArea'],
        ];
        if (isset($formData['details']['reviewDate'])) {
            $data['reviewDate'] = $formData['details']['reviewDate'];
        }
        if (isset($formData['details']['continuationDate'])) {
            $data['expiryDate'] = $formData['details']['continuationDate'];
        }
        if (isset($formData['details']['translateToWelsh'])) {
            $data['translateToWelsh'] = $formData['details']['translateToWelsh'];
        }

        return $data;
    }
}
