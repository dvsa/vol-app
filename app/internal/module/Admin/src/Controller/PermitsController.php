<?php

namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

/**
 * Permits Controller
 *
 * @author Alexander Peshkov <alex.peshkov@valtech.co.uk>
 */
class PermitsController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-permits';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    protected $toggleConfig = [
        'default' => [
            'admin_permits'
        ],
    ];

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-permits',
                'navigationTitle' => 'Permits'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Permits');
        $view = new ViewModel();
        $view->setTemplate('pages/placeholder.phtml');
        return $view;
    }
}
