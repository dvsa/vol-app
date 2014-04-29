<?php

/**
 * AuthorisationFinance Controller
 *
 *
 * @package		selfserve
 * @subpackage          operating-centre
 * @author		S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\Finance;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;

class IndexController extends FormJourneyActionController
{
    protected $messages;
    protected $section = 'finance';

    public function indexAction()
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        $licence = $this->_getLicenceEntity();

        $results = $this->makeRestCall('ApplicationOperatingCentre', 'GET', array('application' => $applicationId));

        $settings = array(
            'sort' => 'address',
            'order' => 'ASC',
            'limit' => 10,
            'page' => 1,
            'url' => $this->getPluginManager()->get('url')
        );

        $tableConfigName = $licence['goodsOrPsv'] == 'psv' ? 'operatingcentrepsv' : 'operatingcentre';

        $table = $this->getServiceLocator()->get('Table')->buildTable($tableConfigName, $results, $settings);

        // render the view
        $view = new ViewModel(Array(
                        'operatingCentres' => $table
                    ));
        $view->setTemplate('self-serve/finance/index');
        return $view;
    }

    /**
     * End of the journey redirect to business type
     */
    public function completeAction()
    {

        // persist data if possible
        $request  = $this->getRequest();

        $this->redirect()->toRoute('selfserve/business', ['step' => 'business_type']);

    }


}
