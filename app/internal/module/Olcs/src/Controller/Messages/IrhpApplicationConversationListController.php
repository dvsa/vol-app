<?php

declare(strict_types=1);

namespace Olcs\Controller\Messages;

use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByLicence as ConversationsByLicenceQuery;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;

class IrhpApplicationConversationListController extends AbstractConversationListController implements IrhpApplicationControllerInterface
{
    protected $navigationId = 'licence_irhp_permits-application';
    protected $listVars = ['licence'];
    protected $listDto = ConversationsByLicenceQuery::class;

    public function getLeftView(): ViewModel
    {
        $view = new ViewModel(['navigationId' => 'irhp_conversations']);
        $view->setTemplate('sections/messages/partials/left');

        return $view;
    }
}
