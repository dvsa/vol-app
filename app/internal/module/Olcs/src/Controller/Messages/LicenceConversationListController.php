<?php

namespace Olcs\Controller\Messages;

use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Dvsa\Olcs\Transfer\Query\Messaging\GetConversationList;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\InternalApplicationsSummary;
use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Olcs\Controller\Interfaces\RightViewProvider;
use Olcs\Listener\RouteParam\Licence;

class LicenceConversationListController extends AbstractInternalController implements LeftViewProvider, LicenceControllerInterface, ToggleAwareInterface
{

    protected $navigationId = 'conversations';
    protected $listVars = ['licence'];
    protected $listDto = GetConversationList::class;
    protected $routeIdentifier = 'messages';
    protected $tableName = 'conversations-list'; 
    protected $tableViewTemplate = 'pages/table';
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::MESSAGING
        ],
    ];

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

    /**
     * Get right view
     *
     * @return ViewModel
     */
    public function getRightView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/licence/partials/right');

        return $view;
    }
}