<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByCaseToLicence as ConversationsByLicenceQuery;
use Olcs\Controller\Interfaces\CaseControllerInterface;

class CaseConversationListController extends AbstractConversationListController implements CaseControllerInterface
{
    protected $navigationId = 'case_conversations';
    protected $listVars = ['case'];
    protected $listDto = ConversationsByLicenceQuery::class;
}
