<?php

namespace Olcs\Controller\Messages;

use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByApplicationToLicence as ConversationsByApplicationToLicenceQuery;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;

class ApplicationConversationListController extends AbstractConversationListController implements ApplicationControllerInterface
{
    protected $navigationId = 'application_conversations';
    protected $listVars = ['application'];
    protected $listDto = ConversationsByApplicationToLicenceQuery::class;
}
