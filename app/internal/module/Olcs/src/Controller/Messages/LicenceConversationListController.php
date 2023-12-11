<?php

namespace Olcs\Controller\Messages;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByLicence as ConversationsByLicenceQuery;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;

class LicenceConversationListController extends AbstractInternalController implements LeftViewProvider, LicenceControllerInterface, ToggleAwareInterface
{
    protected $navigationId = 'conversations';
    protected $listVars = ['licence'];
    protected $listDto = ConversationsByLicenceQuery::class;
    protected $tableName = 'conversations-list';
    protected $tableViewTemplate = 'pages/table';
    protected $toggleConfig = ['default' => [FeatureToggle::MESSAGING]];

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