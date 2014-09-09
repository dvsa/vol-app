<?php

/**
 * Licence Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Licence;

use Olcs\Controller\AbstractController;
use Olcs\Controller\Traits;

/**
 * Licence Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class LicenceController extends AbstractController
{
    use Traits\LicenceControllerTrait,
        Traits\TaskSearchTrait,
        Traits\DocumentSearchTrait;

    public function detailsAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view);
    }

    public function casesAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view);
    }

    public function oppositionAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view);
    }

    public function documentsAction()
    {
        $this->pageLayout = 'licence';

        $filters = $this->mapDocumentFilters(
            array('licenceId' => $this->getFromRoute('licence'))
        );

        $table = $this->getDocumentsTable($filters, false);

        $view = $this->getViewWithLicence(
            array(
                'table' => $table->render(),
                'form'  => $this->getDocumentForm($filters)
            )
        );

        $view->setTemplate('licence/documents');
        $view->setTerminal(
            $this->getRequest()->isXmlHttpRequest()
        );

        return $this->renderView($view);
    }

    public function feesAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view);
    }

    public function busAction()
    {
        //check whether we have a bus reg id or whether we're showing the list
        $licenceId = $this->getFromRoute('licence');

        //no registration id, show list
        $searchData['licence'] = $licenceId;
        $searchData['page'] = $this->getFromRoute('page', 1);
        $searchData['sort'] = $this->getFromRoute('sort', 'regNo');
        $searchData['order'] = $this->getFromRoute('order', 'desc');
        $searchData['limit'] = $this->getFromRoute('limit', 10);
        $searchData['url'] = $this->url();

        $resultData = $this->makeRestCall('BusReg', 'GET', $searchData);
        $table = $this->buildTable('busreg', $resultData, $searchData);

        $view = $this->getViewWithLicence(
            array(
                'table' => $table
            )
        );

        $view->setTemplate('licence/processing');

        return $this->renderView($view);
    }

    /**
     * This method is to assist the heirachical nature of zend
     * navigation when parent pages need to also be siblings
     * from a breadcrumb and navigation point of view.
     *
     * @codeCoverageIgnore
     * @return \Zend\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('licence/details/overview', [], [], true);
    }
}
