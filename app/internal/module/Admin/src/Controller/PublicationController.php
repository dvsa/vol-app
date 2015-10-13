<?php

/**
 * Publication Controller
 */
namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Query\Publication\PendingList;
use Dvsa\Olcs\Transfer\Command\Publication\Publish as PublishCmd;
use Dvsa\Olcs\Transfer\Command\Publication\Generate as GenerateCmd;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;

/**
 * Publication Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicationController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-publication';
    protected $listVars = [];
    protected $inlineScripts = array('indexAction' => ['table-actions', 'file-link']);
    protected $listDto = PendingList::class;
    protected $tableName = 'admin-publication';
    protected $crudConfig = [
        'generate' => ['requireRows' => true],
        'publish' => ['requireRows' => true],
    ];

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

    /**
     * Generate action
     *
     * @return mixed|\Zend\Http\Response
     */
    public function generateAction()
    {
        return $this->processCommand(
            new GenericItem(['id' => 'id']),
            GenerateCmd::class,
            false,
            true,
            'Publication was generated, a new publication was also created'
        );
    }

    /**
     * Publish action
     *
     * @return mixed|\Zend\Http\Response
     */
    public function publishAction()
    {
        return $this->processCommand(new GenericItem(['id' => 'id']), PublishCmd::class);
    }
}
