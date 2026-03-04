<?php

declare(strict_types=1);

namespace Admin\Controller;

use Admin\Form\Model\Form\ClearCache as ClearCacheForm;
use Dvsa\Olcs\Transfer\Command\Cache\Clear as ClearCmd;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class ClearCacheController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/content-management/clear-cache';

    #[\Override]
    public function getLeftView(): ViewModel
    {
        $view = new ViewModel([
            'navigationId' => 'admin-dashboard/content-management',
            'navigationTitle' => 'Clear caches',
        ]);
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * @return Response|ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        $form = $this->getForm(ClearCacheForm::class);

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $cacheIds = (array) (($post->get('clear-cache') ?? [])['cacheIds'] ?? []);

            $response = $this->handleCommand(ClearCmd::create(['cacheIds' => $cacheIds]));

            if ($response->isOk()) {
                foreach ($response->getResult()['messages'] ?? [] as $message) {
                    $this->flashMessengerHelperService->addSuccessMessage($message);
                }
            } else {
                $this->flashMessengerHelperService->addCurrentUnknownError();
            }

            return $this->redirect()->toRoute('admin-dashboard/admin-clear-cache');
        }

        $this->placeholder()->setPlaceholder('pageTitle', 'Clear caches');
        $this->placeholder()->setPlaceholder('contentTitle', 'Clear caches');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('admin/pages/clear-cache/index');

        return $this->viewBuilder()->buildView($view);
    }
}
