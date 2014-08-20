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
    protected $title;
    protected $subtitle;

    public function getViewWithLicence()
    {
        $licence = $this->getLicence($this->getFromRoute('licence'));

        $view = $this->getView(['licence' => $licence]);

        $this->title = $view->licence['licenceNumber'];
        $this->subTitle = $view->licence['goodsOrPsv'] . ', ' . $view->licence['licenceType']
            . ', ' . $view->licence['licenceStatus'];

        return $view;
    }

    public function indexAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view, $this->title, $this->subTitle);
    }

    public function detailsAction()
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
}
