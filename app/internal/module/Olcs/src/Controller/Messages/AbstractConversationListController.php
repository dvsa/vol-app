<?php

namespace Olcs\Controller\Messages;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\MessagingControllerInterface;
use Olcs\Form\Model\Form\ConversationFilter;

abstract class AbstractConversationListController extends AbstractInternalController implements LeftViewProvider, ToggleAwareInterface, MessagingControllerInterface
{
    protected $tableName = 'conversations-list';
    protected $tableViewTemplate = 'pages/table';
    protected $toggleConfig = ['default' => [FeatureToggle::MESSAGING]];
    protected $inlineScripts = [
        'indexAction' => ['forms/filter'],
    ];

    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $formAction = $this->url()->fromRoute(
            $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            $this->params()->fromRoute(),
        );
        $filterForm = $this->getForm(ConversationFilter::class);
        $filterForm->setAttribute('action', $formAction);
        $filterForm->setData($this->getRequest()->getQuery());

        $view = new ViewModel(['navigationId' => $this->navigationId]);
        $view->setTemplate('sections/messages/partials/left');
        $this->placeholder()->setPlaceholder('tableFilters', $filterForm);

        return $view;
    }

    /**
     * Get right view
     *
     * @return ViewModel
     */
    public function getRightView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/application/partials/right');

        return $view;
    }

    public function modifyListQueryParameters($parameters)
    {
        $filterForm = $this->getForm(ConversationFilter::class);
        $filterForm->setData($this->getRequest()->getQuery());

        $status = $filterForm->get('status')->getValue();
        if ($filterForm->isValid() && $status !== '') {
            $parameters['statuses'] = [$status];
        }

        return parent::modifyListQueryParameters($parameters);
    }
}
