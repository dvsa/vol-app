<?php

namespace Olcs\Controller\TransportManager\Details;

use Common\Controller\Traits\GenericUpload;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Command\TmQualification\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\TmQualification\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\TmQualification\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Tm\Documents as DocumentsQry;
use Dvsa\Olcs\Transfer\Query\TmQualification\TmQualification as TmQualificationQry;
use Dvsa\Olcs\Transfer\Query\TmQualification\TmQualificationsList as TmQualificationsListQry;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Olcs\Data\Mapper\TmQualification as Mapper;
use Olcs\Form\Model\Form\Qualification as TmQualificationForm;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;
use Zend\Form\FormInterface;
use Zend\View\Model\ViewModel;

/**
 * Transport Manager Details Competence Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsCompetenceController extends AbstractInternalController implements
    TransportManagerControllerInterface,
    LeftViewProvider
{
    use GenericUpload;

    protected $navigationId = 'transport_manager_details_competences';

    /**
     * @var string
     */
    protected $documents = null;

    /* for list */
    protected $listDto = TmQualificationsListQry::class;
    protected $listVars = ['transportManager'];
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'sections/transport-manager/pages/tm-competence';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'tm.qualifications';

    protected $inlineScripts = [
        'indexAction' => ['forms/crud-table-handler']
    ];

    /* for edit */
    protected $formClass = TmQualificationForm::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add qualification';
    protected $editContentTitle = 'Edit qualification';

    /* for add */
    protected $createCommand = CreateDto::class;

    /* for view */
    protected $editViewTemplate = 'pages/crud-form';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = TmQualificationQry::class;
    protected $itemParams = ['id' => 'id'];

    protected $defaultData = [
        'transportManager' => 'route',
    ];

    protected $deleteCommand = DeleteDto::class;
    protected $deleteParams = ['ids' => 'id'];
    protected $hasMultiDelete = true;

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('contentTitle', 'Competences');
        $response = $this->index(
            $this->listDto,
            new GenericList($this->listVars, $this->defaultTableSortField),
            $this->tableViewPlaceholderName,
            $this->tableName,
            $this->tableViewTemplate,
            $this->filterForm
        );

        $form = $this->getForm('CertificateUpload');
        $this->placeholder()->setPlaceholder('form', $form);
        $this->processFiles(
            $form,
            'file',
            array($this, 'processCertificateFileUpload'),
            array($this, 'deleteFile'),
            array($this, 'getDocuments')
        );
        return $response;
    }

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/transport-manager/partials/details-left');

        return $view;
    }

    public function editAction()
    {
        return $this->edit(
            $this->formClass,
            $this->itemDto,
            new GenericItem($this->itemParams),
            $this->updateCommand,
            $this->mapperClass,
            $this->editViewTemplate,
            'Updated record',
            $this->editContentTitle
        );
    }

    protected function alterFormForEdit(FormInterface $form, $data)
    {
        $form->get('form-actions')->remove('addAnother');
        return $form;
    }

    /**
     * Get transport manager documents
     *
     * @return array
     */
    public function getDocuments()
    {
        if ($this->documents === null) {
            $queryToSend = $this->getServiceLocator()
                ->get('TransferAnnotationBuilder')
                ->createQuery(
                    DocumentsQry::create(
                        [
                            'id' => $this->params()->fromRoute('transportManager')
                        ]
                    )
                );

            /** @var Response $response */
            $response = $this->getServiceLocator()->get('QueryService')->send($queryToSend);

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }
            $mappedResults = [];
            if ($response->isOk()) {
                $mappedResults = Mapper::mapFromDocumentsResult($response->getResult());
            }
            $this->documents = $mappedResults;
        }
        return $this->documents;
    }

    /**
     * Handle the file upload
     *
     * @param array $file
     * @return array
     */
    public function processCertificateFileUpload($file)
    {
        $tmId = $this->params()->fromRoute('transportManager');

        $data = $this->getServiceLocator()->get('Helper\TransportManager')
            ->getCertificateFileData($tmId, $file);

        return $this->uploadFile($file, $data);
    }

    protected function alterFormForIndex(FormInterface $form, $data)
    {
        if (!is_null($data['removedDate'])) {
            $form->setOption('readonly', true);
        }

        return $form;
    }

    protected function alterTable($table, $data)
    {
        if (isset($data['extra']['transportManager']['removedDate'])) {
            /* @var $table \Common\Service\Table\TableBuilder */
            $table->setDisabled(true);

            // of readonly then remove hyperlink from column
            $column = $table->getColumn('qualificationType');
            $column['formatter'] = function ($row) {
                return $row['qualificationType']['description'];
            };
            $table->setColumn('qualificationType', $column);
        }

        return $table;
    }
}
