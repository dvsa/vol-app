<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Olcs\Controller\Interfaces\LicenceControllerInterface;

class LicenceConversationMessagesController
    extends AbstractConversationMessagesController
    implements LicenceControllerInterface
{
    protected $navigationId = 'conversations';
    protected $topNavigationId = 'licence';
    protected $listVars = ['licence', 'conversation'];
}
