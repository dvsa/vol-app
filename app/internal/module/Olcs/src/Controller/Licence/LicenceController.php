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
use Olcs\Controller\Traits\TaskSearchTrait;

/**
 * Licence Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class LicenceController extends AbstractController
{
    use TaskSearchTrait;

    public function getViewWithLicence($variables = array())
    {
        $licence = $this->getLicence($this->getFromRoute('licence'));

        if ($licence['goodsOrPsv'] == 'Goods') {
            $this->getServiceLocator()->get('Navigation')->findOneBy('id', 'licence_bus')->setVisible(0);
        }

        $variables['licence'] = $licence;

        $view = $this->getView($variables);

        $this->pageTitle = $view->licence['licNo'];
        $this->pageSubTitle = $view->licence['goodsOrPsv']['id'] . ', ' . $view->licence['licenceType']['id']
            . ', ' . $view->licence['status']['id'];

        return $view;
    }

    public function indexAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view);
    }

    public function editAction()
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

    public function documentsAction()
    {
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

        return $this->renderView($view);
    }

    public function processingAction()
    {
        $filters = $this->mapTaskFilters();
        $filters['licenceId'] = $this->getFromRoute('licence');

        $table = $this->getTaskTable($filters, false);

        // the table's nearly all good except we don't want
        // a couple of columns
        $table->removeColumn('name');
        $table->removeColumn('link');

        $view = $this->getViewWithLicence(array(
            'table' => $table->render(),
            'form'  => $this->getTaskForm($filters),
            'inlineScript' => $this->loadScripts(['tasks'])
        ));

        $view->setTemplate('licence/processing');

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
        $view = $this->getViewWithLicence();
        $view->setTemplate('licence/index');

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
