<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Traits\ApplicationControllerTrait;

class ApplicationConversationMessagesController
    extends AbstractConversationMessagesController
    implements ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $navigationId = 'application_conversations';
    protected $topNavigationId = 'application';
    protected $listVars = ['lva-application', 'conversation'];
}
