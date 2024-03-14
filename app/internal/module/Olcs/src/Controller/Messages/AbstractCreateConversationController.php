<?php

namespace Olcs\Controller\Messages;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Data\Mapper\DefaultMapper;
use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Create;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByApplicationToOrganisation;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByLicenceToOrganisation;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByCaseToOrganisation;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\MessagingControllerInterface;
use Olcs\Form\Model\Form\Conversation;
use RuntimeException;

class AbstractCreateConversationController extends AbstractInternalController implements LeftViewProvider, ToggleAwareInterface, MessagingControllerInterface
{
    protected $mapperClass = DefaultMapper::class;

    protected $createCommand = Create::class;

    protected $formClass = Conversation::class;

    protected $toggleConfig = [
        'default' => [
            FeatureToggle::MESSAGING,
        ],
    ];

    protected $inlineScripts = [
        'addAction' => ['forms/message-categories'],
    ];

    protected string $application  = '';
    protected string $licence  = '';

    public function getLeftView(): ViewModel
    {
        $view = new ViewModel(['navigationId' => $this->navigationId]);
        $view->setTemplate('sections/messages/partials/left');

        return $view;
    }

    public function onDispatch(MvcEvent $e)
    {
        $params = $e->getRouteMatch()->getParams();
        if (isset($params['licence'])) {
            $this->licence = $params['licence'];
        }
        if (isset($params['application'])) {
            $this->application = $params['application'];
        }
        $this->defaultData['application'] = $this->application;
        $this->defaultData['licence'] = $this->licence;

        return parent::onDispatch($e);
    }
}
