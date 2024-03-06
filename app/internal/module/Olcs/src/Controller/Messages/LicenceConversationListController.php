<?php

namespace Olcs\Controller\Messages;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByLicence as ConversationsByLicenceQuery;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Interfaces\MessagingControllerInterface;

class LicenceConversationListController extends AbstractConversationListController implements LicenceControllerInterface
{
    protected $navigationId = 'conversations';
    protected $listVars = ['licence'];
    protected $listDto = ConversationsByLicenceQuery::class;
}
