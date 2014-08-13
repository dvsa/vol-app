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
        $licence = $this->getLicence($this->getFromRoute('licence'));

        $view = $this->getView(['licence' => $licence]);

        return $view;
    }

    public function indexAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $view;
    }

    public function editAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $view;
    }

    public function casesAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $view;
    }

    public function documentsAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $view;
    }

    public function processingAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $view;
    }

    public function feesAction()
    {
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

        // @todo need to define bundle here to get types/statuses etc

        $licence = $this->makeRestCall('Licence', 'GET', array('id' => $id));

        return $licence;
    }
}
