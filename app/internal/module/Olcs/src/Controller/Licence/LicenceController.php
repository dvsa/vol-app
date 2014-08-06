<?php

/**
 * Licence Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Licence;

use Common\Controller\FormActionController as AbstractFormActionController;
use Zend\View\Model\ViewModel;

/**
 * Licence Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class LicenceController extends AbstractFormActionController
{
    public function getViewWithLicence()
    {
        $licence = $this->getLicence($this->getFromRoute('id'));

        $view = $this->getView(['licence' => $licence]);
        $view->setTemplate('licence/index');

        return $view;
    }

    public function indexAction()
    {
        $this->setBreadcrumb(array('licence/overview' => array('id' => $this->getFromRoute('id'))));

        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $view;
    }

    public function editAction()
    {
        $this->setBreadcrumb(array('licence/edit' => array('id' => $this->getFromRoute('id'))));

        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $view;
    }

    public function casesAction()
    {
        $this->setBreadcrumb(array('licence/cases' => array('id' => $this->getFromRoute('id'))));

        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $view;
    }

    public function documentsAction()
    {
        $this->setBreadcrumb(array('licence/documents' => array('id' => $this->getFromRoute('id'))));

        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $view;
    }

    public function processingAction()
    {
        $this->setBreadcrumb(array('licence/edit' => array('id' => $this->getFromRoute('id'))));

        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $view;
    }

    public function feesAction()
    {
        $this->setBreadcrumb(array('licence/edit' => array('id' => $this->getFromRoute('id'))));

        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $view;
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
        return $this->redirect()->toRoute('licence/overview', [], [], true);
    }


    /**
     * @codeCoverageIgnore
     *
     * @param array $params
     * @return \Zend\View\Model\ViewModel
     */
    public function getView(array $params = null)
    {
        return new ViewModel($params);
    }

    /**
     * Gets the licence by ID.
     *
     * @param integer $id
     * @return array
     */
    public function getLicence($id)
    {
        /* $bundle = array(
            'children' => array(
                'categories' => array(
                    'properties' => array(
                        'id',
                        'name'
                    )
                ),
                'licence' => array(
                    'properties' => 'ALL',
                    'children' => array(
                        'trafficArea' => array(
                            'properties' => 'ALL'
                        ),
                        'organisation' => array(
                            'properties' => 'ALL'
                        )
                    )
                )
            )
        ); */

        $licence = $this->makeRestCall('Licence', 'GET', array('id' => $id));

        return $licence;
    }
}