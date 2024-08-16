<?php

namespace Olcs\Controller\Application\Processing;

use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Command\Publication\DeletePublicationLink;
use Dvsa\Olcs\Transfer\Command\Publication\UpdatePublicationLink;
use Dvsa\Olcs\Transfer\Query\Publication\PublicationLink as PublicationLinkDto;
use Dvsa\Olcs\Transfer\Query\Publication\PublicationLinkList;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\PublicationLink as PublicationLinkMapper;
use Olcs\Form\Model\Form\Publication as PublicationForm;
use Olcs\Form\Model\Form\PublicationNotNew as PublicationNotNewForm;

class ApplicationProcessingPublicationsController extends AbstractInternalController implements
    ApplicationControllerInterface,
    LeftViewProvider
{
    protected $navigationId = 'application_processing_publications';
    protected $defaultTableSortField = 'createdOn';
    protected $tableName = 'publication';
    protected $listDto = PublicationLinkList::class;
    protected $itemDto = PublicationLinkDto::class;
    protected $listVars = ['application'];
    protected $mapperClass = PublicationLinkMapper::class;
    protected $formClass = PublicationForm::class;
    protected $updateCommand = UpdatePublicationLink::class;
    protected $deleteCommand = DeletePublicationLink::class;
    protected $inlineScripts = ['indexAction' => ['table-actions']];
    protected $addContentTitle = 'Add publication';
    protected $editContentTitle = 'Edit publication';

    /**
     * get Method Left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }

    /**
     * Edit Action
     *
     * @return array|\Laminas\View\Model\ViewModel
     */
    public function editAction()
    {
        $publicationLink = $this->getPublicationLink();

        //if publication status is not new, switch the form
        if (!$publicationLink['isNew']) {
            $this->formClass = PublicationNotNewForm::class;
        }

        return parent::editAction();
    }

    /**
     * Gets PublicationLink information
     *
     * @return array|mixed
     */
    private function getPublicationLink()
    {
        $params = ['id' => $this->params()->fromRoute('id')];
        $response = $this->handleQuery(PublicationLinkDto::create($params));

        if ($response->isServerError() || $response->isClientError()) {
            $this->flashMessengerHelperService->addErrorMessage('unknown-error');
        }

        return $response->getResult();
    }

    /**
     * Override in derived classes to alter table *presentation* based on the
     * list data
     *
     * @param TableBuilder $table table
     * @param array $data  table
     *
     * @return TableBuilder
     */
    protected function alterTable($table, $data)
    {
        /* @var $table \Common\Service\Table\TableBuilder */
        $column = $table->getColumn('createdOn');
        $column['lva'] = 'lva-application';
        $table->setColumn('createdOn', $column);

        return parent::alterTable($table, $data);
    }
}
