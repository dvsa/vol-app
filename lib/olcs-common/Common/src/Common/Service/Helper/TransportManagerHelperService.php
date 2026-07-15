<?php

namespace Common\Service\Helper;

use Common\Service\Cqrs\Query\CachingQueryService as QueryService;
use Common\Service\Data\CategoryDataService;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;

/**
 * Transport Manager Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerHelperService
{
    /** @var TransferAnnotationBuilder */
    protected $transferAnnotationBuilder;

    /** @var QueryService */
    protected $queryService;

    public function __construct(
        TransferAnnotationBuilder $transferAnnotationBuilder,
        QueryService $queryService,
        private FormHelperService $formHelper,
        private DateHelperService $dateHelper,
        private TranslationHelperService $translationHelper,
        private UrlHelperService $urlHelper,
        private TableFactory $tableService
    ) {
        $this->transferAnnotationBuilder = $transferAnnotationBuilder;
        $this->queryService = $queryService;
    }

    /**
     * @param string[] $file
     *
     * @psalm-param 111 $tmId
     * @psalm-param array{name: 'foo.txt'} $file
     *
     * @return (int|mixed)[]
     *
     * @psalm-return array{transportManager: mixed, description: mixed, issuedDate: mixed, category: 5, subCategory: 98}
     */
    public function getCertificateFileData($tmId, $file): array
    {
        return [
            'transportManager' => $tmId,
            'description' => $file['name'],
            'issuedDate' => $this->dateHelper->getDate('Y-m-d H:i:s'),
            'category'    => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION
        ];
    }

    public function removeTmTypeBothOption(Element $tmType): void
    {
        $this->formHelper->removeOption($tmType, 'tm_t_b');
    }

    public function populateOtherLicencesTable(Fieldset $otherLicencesField, TableBuilder $otherLicencesTable): void
    {
        $this->formHelper->populateFormTable($otherLicencesField, $otherLicencesTable);
    }

    /**
     * @psalm-param 111 $tmId
     *
     * @return (int|mixed)[]
     *
     * @psalm-return array{transportManager: mixed, issuedDate: mixed, category: 5, subCategory: 100}
     */
    public function getResponsibilityFileData($tmId): array
    {
        return [
            'transportManager' => $tmId,
            'issuedDate' => $this->dateHelper->getDate(\DateTime::W3C),
            'category'    => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL
        ];
    }

    /**
     * @psalm-param 111 $transportManagerId
     */
    public function getConvictionsAndPenaltiesTable($transportManagerId)
    {
        $result = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\PreviousConviction\GetList::create(['transportManager' => $transportManagerId])
        );
        $results = $result['results'];

        return $this->tableService->prepareTable(
            'tm.convictionsandpenalties',
            $results
        );
    }

    /**
     * Execute a query DTO
     *
     * @param \Dvsa\Olcs\Transfer\Query\QueryInterface $dto
     *
     * @return array of results
     * @throws \RuntimeException
     */
    protected function handleQuery($dto)
    {
        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->queryService->send($this->transferAnnotationBuilder->createQuery($dto));

        if (!$response->isOk()) {
            throw new \RuntimeException('Error fetching query ' . $dto::class);
        }

        return $response->getResult();
    }


    /**
     * @psalm-param 111 $transportManagerId
     */
    public function getPreviousLicencesTable(int $transportManagerId)
    {
        $result = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\OtherLicence\GetList::create(['transportManager' => $transportManagerId])
        );

        $results = $result['results'];

        return $this->tableService->prepareTable(
            'tm.previouslicences',
            $results
        );
    }

    /**
     * This method superseeds alterPreviousHistoryFieldset
     *
     * @param \Laminas\Form\Fieldset $fieldset
     * @param array               $tm
     */
    public function alterPreviousHistoryFieldsetTm($fieldset, $tm): void
    {
        $convictionsAndPenaltiesTable = $this->tableService->prepareTable(
            'tm.convictionsandpenalties',
            $tm['previousConvictions']
        );
        $previousLicencesTable = $this->tableService->prepareTable(
            'tm.previouslicences',
            $tm['otherLicences']
        );

        $this->populatePreviousHistoryTables($fieldset, $convictionsAndPenaltiesTable, $previousLicencesTable);

        $this->setConvictionsReadMoreLink($fieldset);
    }

    /**
     * @psalm-param 111 $tmId
     */
    public function alterPreviousHistoryFieldset(\Laminas\Form\Fieldset $fieldset, $tmId): void
    {
        $transportManager = $this->getTransportManager($tmId);
        $convictionsAndPenaltiesTable = $this->getConvictionsAndPenaltiesTable($transportManager['id']);
        $previousLicencesTable = $this->getPreviousLicencesTable($transportManager['id']);
        $this->populatePreviousHistoryTables($fieldset, $convictionsAndPenaltiesTable, $previousLicencesTable);

        $fieldset->get('hasConvictions')->unsetValueOption('Y');
        $fieldset->get('hasConvictions')->unsetValueOption('N');
        $fieldset->get('convictions')->removeAttribute('class');
        $fieldset->get('hasPreviousLicences')->unsetValueOption('Y');
        $fieldset->get('hasPreviousLicences')->unsetValueOption('N');
        $fieldset->get('previousLicences')->removeAttribute('class');

        $this->setConvictionsReadMoreLink($fieldset);

        if (!is_null($transportManager['removedDate'])) {
            $fieldset->get('convictions')->get('table')->getTable()->setDisabled(true);
            $fieldset->get('previousLicences')->get('table')->getTable()->setDisabled(true);

            // remove hyperlinks from table
            $column = $fieldset->get('convictions')->get('table')->getTable()->getColumn('convictionDate');
            unset($column['type']);
            $fieldset->get('convictions')->get('table')->getTable()->setColumn('convictionDate', $column);

            $column = $fieldset->get('previousLicences')->get('table')->getTable()->getColumn('licNo');
            unset($column['type']);
            $fieldset->get('previousLicences')->get('table')->getTable()->setColumn('licNo', $column);
        }
    }

    private function getTransportManager($tmId)
    {
        return $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Tm\TransportManager::create(
                [
                    'id' => $tmId
                ]
            )
        );
    }

    /**
     * Prepare Tm other employment table
     *
     * @param \Laminas\Form\Element $element
     * @param array              $tm      Transport Manager data
     */
    public function prepareOtherEmploymentTableTm($element, $tm): void
    {
        $table = $this->tableService->prepareTable('tm.employments', $tm['employments']);

        $this->formHelper->populateFormTable($element, $table, 'employment');
    }

    /**
     * @return (array|mixed)[]
     *
     * @psalm-return array{'tm-employment-details': array{id: mixed, version: mixed, position: mixed, hoursPerWeek: mixed}, 'tm-employer-name-details': array{employerName: mixed}, address?: mixed}
     */
    public function getOtherEmploymentData($id): array
    {
        $employment = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\TmEmployment\GetSingle::create(
                [
                    'id' => $id
                ]
            )
        );

        $data = [
            'tm-employment-details' => [
                'id' => $employment['id'],
                'version' => $employment['version'],
                'position' => $employment['position'],
                'hoursPerWeek' => $employment['hoursPerWeek'],
            ],
            'tm-employer-name-details' => [
                'employerName' => $employment['employerName']
            ]
        ];

        if (isset($employment['contactDetails']['address'])) {
            $data['address'] = $employment['contactDetails']['address'];
        }

        return $data;
    }

    private function setConvictionsReadMoreLink(\Laminas\Form\Fieldset $fieldset): void
    {
        $hasConvictions = $fieldset->get('hasConvictions');
        $routeParam = $this->translationHelper->translate('convictions-and-penalties-guidance-route-param');
        $convictionsReadMoreRoute = $this->urlHelper->fromRoute(
            'guides/guide',
            ['guide' => $routeParam]
        );
        $hint = $this->translationHelper->translateReplace(
            'transport-manager.convictions-and-penalties.form.radio.hint',
            [$convictionsReadMoreRoute]
        );
        $hasConvictions->setOption('hint', $hint);
    }

    private function populatePreviousHistoryTables(\Laminas\Form\Fieldset $fieldset, $convictionsAndPenaltiesTable, $previousLicencesTable): void
    {
        $this->formHelper->populateFormTable(
            $fieldset->get('convictions'),
            $convictionsAndPenaltiesTable,
            'convictions'
        );
        $this->formHelper->populateFormTable(
            $fieldset->get('previousLicences'),
            $previousLicencesTable,
            'previousLicences'
        );
    }
}
