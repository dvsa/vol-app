<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;

class IrhpApplicationConversationMessagesController extends AbstractConversationMessagesController implements IrhpApplicationControllerInterface
{
    protected $navigationId = 'irhp_conversations';
    protected $topNavigationId = 'licence_irhp_permits-application';
    protected $listVars = ['licence', 'conversation'];

    protected function getConversationViewRoute(): string
    {
        return 'licence/irhp-application-conversation/view';
    }
}
