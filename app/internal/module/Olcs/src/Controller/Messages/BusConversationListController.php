<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByLicence as ConversationsByLicenceQuery;
use Olcs\Controller\Interfaces\BusRegControllerInterface;

class BusConversationListController extends AbstractConversationListController implements BusRegControllerInterface
{
    protected $navigationId = 'bus_conversations';
    protected $listVars = ['licence'];
    protected $listDto = ConversationsByLicenceQuery::class;
}
