<?php

namespace Olcs\Controller\Messages;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableBuilder;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Query\Messaging\Messages\ByConversation;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Olcs\Mvc\Controller\Plugin\Table;

class LicenceConversationMessagesController extends AbstractInternalController implements LeftViewProvider, LicenceControllerInterface, ToggleAwareInterface
{
    protected $navigationId = 'conversations';
    protected $listVars = ['licence', 'conversation'];
    protected $listDto = ByConversation::class;
    protected $tableName = 'messages-list';
    protected $routeIdentifier = 'messages';
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::MESSAGING
        ],
    ];
    protected ScriptFactory $scriptFactory;

    public function __construct(
        TranslationHelperService    $translationHelper,
        FormHelperService           $formHelper,
        FlashMessengerHelperService $flashMessenger,
        Navigation                  $navigation,
        ScriptFactory               $scriptFactory
    )
    {
        parent::__construct($translationHelper, $formHelper, $flashMessenger, $navigation);

        $this->scriptFactory = $scriptFactory;
    }

    /**
     * @inheritDoc
     */
    public function indexAction()
    {
        $this->scriptFactory->loadFiles(['table-actions']);

        if (!$this->getRequest()->isPost()) {
            return parent::indexAction();
        }

        $action = strtolower($this->params()->fromPost('action'));
        switch ($action) {
            case 'end and archive conversation':
                $params = [
                    'licence' => $this->params()->fromRoute('licence'),
                    'conversation' => $this->params()->fromRoute('conversation'),
                    'action' => $this->params()->fromRoute('confirm'),
                ];
                return $this->redirect()->toRoute('licence/conversation/close', $params);
        }
    }

    /**
     * @param TableBuilder $table
     * @param array $data
     */
    protected function alterTable($table, $data): TableBuilder
    {
        if (!$data['extra']['conversation']['isClosed']) {
            return $table;
        }

        $crud = $table->getSetting('crud');
        $crud['actions']['end and archive conversation']['class'] .= ' govuk-button--disabled';
        $crud['actions']['end and archive conversation']['disabled'] = 'disabled';
        $table->setSetting('crud', $crud);

        return $table;
    }

    public function getLeftView(): ViewModel
    {
        $view = new ViewModel();
        $view->setTemplate('sections/messages/partials/left');

        return $view;
    }

    public function getRightView(): ViewModel
    {
        $view = new ViewModel();
        $view->setTemplate('sections/licence/partials/right');

        return $view;
    }
}
