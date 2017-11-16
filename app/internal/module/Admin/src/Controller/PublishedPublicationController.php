<?php

/**
 * Published Publication Controller
 */

namespace Admin\Controller;

use Admin\Form\Model\Form\PublishedPublicationFilter;
use Dvsa\Olcs\Transfer\Query\Publication\PublishedList;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

/**
 * Published Publication Controller
 *
 * @author Richard Ward <richard.ward@bjss.com>
 */
class PublishedPublicationController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-publication/published';
    protected $inlineScripts = array('indexAction' => ['file-link']);
    protected $listDto = PublishedList::class;
    protected $tableName = 'admin-published-publication';
    protected $filterForm = PublishedPublicationFilter::class;

    /**
     * Get view for menu on left of page
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-publication',
                'navigationTitle' => 'Publications'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }
}
