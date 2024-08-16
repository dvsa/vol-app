<?php

namespace Admin\Controller;

use Admin\Data\Mapper\LocalAuthority as LocalAuthorityMapper;
use Admin\Form\Model\Form\LocalAuthority as LocalAuthorityForm;
use Dvsa\Olcs\Transfer\Command\LocalAuthority\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\LocalAuthority\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\LocalAuthority\LocalAuthorityList as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

class LocalAuthorityController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    // list
    protected $tableName = 'admin-local-authority';
    protected $defaultTableSortField = 'id';
    protected $defaultTableOrderField = 'ASC';
    protected $listDto = ListDto::class;

    // add/edit
    protected $itemDto = ItemDto::class;
    protected $formClass = LocalAuthorityForm::class;
    protected $mapperClass = LocalAuthorityMapper::class;
    protected $updateCommand = UpdateDto::class;

    protected $editContentTitle = 'Edit Local Authority';

    protected $tableViewTemplate = 'pages/local-authority/index';

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-bus-registration/notice-period',
                'navigationTitle' => 'Local Authorities (LTA)'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }
}
