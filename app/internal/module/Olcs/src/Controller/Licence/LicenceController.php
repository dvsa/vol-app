<?php

/**
 * Licence Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Licence;

use Common\Controller\FormActionController as AbstractFormActionController;
use Zend\View\Model\ViewModel;
use Olcs\Controller\AbstractController;

/**
 * Licence Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class LicenceController extends AbstractController
{
    protected $title;
    protected $subtitle;

    public function getViewWithLicence()
    {
        $licence = $this->getLicence($this->getFromRoute('licence'));

        $view = $this->getView(['licence' => $licence]);

        $this->title = $view->licence['licNo'];
        $this->subTitle = $view->licence['goodsOrPsv']['id'] . ', ' . $view->licence['licenceType']['id']
            . ', ' . $view->licence['status']['id'];

        return $view;
    }

    public function indexAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view, $this->title, $this->subTitle);
    }

    public function editAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view, $this->title, $this->subTitle);
    }

    public function casesAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view, $this->title, $this->subTitle);
    }

    public function documentsAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view, $this->title, $this->subTitle);
    }

    public function processingAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view, $this->title, $this->subTitle);
    }

    public function feesAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view, $this->title, $this->subTitle);
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
        $bundle = array(
            'properties' => 'ALL',
            'children' => array(
                'status' => array(
                    'properties' => array('id')
                ),
                'goodsOrPsv' => array(
                    'properties' => array('id')
                ),
                'licenceType' => array(
                    'properties' => array('id')
                ),
                'trafficArea' => array(
                    'properties' => 'ALL'
                ),
                'organisation' => array(
                    'properties' => 'ALL'
                )
            )
        );

        $licence = $this->makeRestCall('Licence', 'GET', array('id' => $id), $bundle);

        return $licence;
    }
}
