<?php

namespace Olcs\Controller\Messages;

use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\InternalApplicationsSummary;

class LicenceDisableConversationListController extends AbstractInternalController implements LeftViewProvider
{

    protected $navigationId = 'conversation_list_disable_messaging';
    protected $listVars = ['licence'];
    protected $routeIdentifier = 'conversation/disable';

    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/messages/partials/left');

        return $view;
    }

}