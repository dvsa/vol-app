<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\Cache\Clear;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class CacheClearController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-cache-clear';

    #[\Override]
    public function getLeftView()
    {
        $view = new ViewModel([
            'navigationId' => $this->navigationId,
            'navigationTitle' => 'Clear cache',
        ]);

        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    #[\Override]
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Clear cache');

        if (!$this->getRequest()->isPost()) {
            return new ViewModel();
        }

        // Clear the namespaces used by translations and feature-toggle/system-parameter changes.
        $response = $this->handleCommand(
            Clear::create([
                'namespace' => 'translation_key,translation_replacement,sys_param,sys_param_list',
                'dryRun' => false,
            ])
        );

        if ($response->isServerError() || $response->isClientError()) {
            $this->flashMessengerHelperService
                ->addErrorMessage('Cache could not be cleared');
        }

        if ($response->isOk()) {
            $this->flashMessengerHelperService
                ->addSuccessMessage('Cache cleared successfully');
        }

        return $this->redirect()->toRoute(
            'admin-dashboard/admin-cache-clear'
        );
    }
}
