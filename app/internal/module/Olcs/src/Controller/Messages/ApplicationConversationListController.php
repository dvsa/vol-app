<?php

namespace Olcs\Controller\Messages;

use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Dvsa\Olcs\Transfer\Query\Messaging\GetConversationList;
use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Interfaces\RightViewProvider;
use Olcs\Listener\RouteParam\Licence;

class ApplicationConversationListController extends AbstractInternalController implements LeftViewProvider, ApplicationControllerInterface, ToggleAwareInterface
{

    protected $navigationId = 'application_conversations';
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
        $view->setTemplate('sections/application/partials/right');

        return $view;
    }
}