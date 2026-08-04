<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\Cache\Clear;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;

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
        return $this->confirmCommand(
            new AddFormDefaultData([
                'namespace' => 'translation_key,translation_replacement,sys_param,sys_param_list',
                'dryRun' => false,
            ]),
            Clear::class,
            'Clear cache',
            'Are you sure you want to clear the cache?',
            'Cache cleared successfully',
            'Clear cache'
        );
    }
}
