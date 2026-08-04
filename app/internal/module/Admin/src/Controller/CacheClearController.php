<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\Cache\Clear;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class CacheClearController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-cache-clear';

    private const CACHE_NAMESPACE_MAP = [
        'translations' => [
            'translation_key',
            'translation_replacement',
        ],
        'system_parameters' => [
            'sys_param',
            'sys_param_list',
        ],
        'cqrs' => [
            'cqrs',
        ],
    ];

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
        $this->placeholder()->setPlaceholder('contentTitle', 'Clear cache');

        $request = $this->getRequest();

        $form = $this->formHelperService
            ->createFormWithRequest('CacheClear', $request);

        $formActions = $form->get('form-actions');

        $formActions->get('submit')
            ->setLabel('Clear cache')
            ->setAttribute('aria-label', 'Clear cache');

        $formActions->remove('cancel');
        $formActions->remove('addAnother');

        $isPost = $request->isPost();

        if ($isPost) {
            $form->setData((array) $request->getPost());
        }

        if ($isPost && $form->isValid()) {
            $data = $form->getData();
            $selectedTypes = $data['cacheTypes'] ?? [];

            $namespaces = [];

            foreach ($selectedTypes as $selectedType) {
                $namespaces = array_merge(
                    $namespaces,
                    self::CACHE_NAMESPACE_MAP[$selectedType] ?? []
                );
            }

            $response = $this->handleCommand(
                Clear::create([
                    'namespace' => implode(',', array_unique($namespaces)),
                    'dryRun' => false,
                ])
            );

            if ($response->isOk()) {
                $this->flashMessengerHelperService
                    ->addSuccessMessage('Cache cleared successfully');
            } elseif ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelperService
                    ->addErrorMessage('Cache could not be cleared');
            }

            return $this->redirect()->toRoute(
                'admin-dashboard/admin-cache-clear'
            );
        }

        $view = new ViewModel([
            'form' => $form,
        ]);

        $view->setTemplate('pages/form');

        return $view;
    }
}
