<?php

namespace Olcs\Controller\Messages;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\MessagingControllerInterface;

abstract class AbstractConversationListController extends AbstractInternalController implements LeftViewProvider, ToggleAwareInterface, MessagingControllerInterface
{
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
        $view = new ViewModel(['navigationId' => $this->navigationId]);
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
