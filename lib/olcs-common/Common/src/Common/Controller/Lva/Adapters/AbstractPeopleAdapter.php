<?php

namespace Common\Controller\Lva\Adapters;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Interfaces\PeopleAdapterInterface;
use Common\Controller\Plugin\HandleQuery;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Response;
use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePeople;
use Dvsa\Olcs\Transfer\Command\Application\CreatePeople as CreatePeopleApplication;
use Dvsa\Olcs\Transfer\Command\Licence\DeletePeople;
use Dvsa\Olcs\Transfer\Command\Application\DeletePeople as DeletePeopleApplication;
use Dvsa\Olcs\Transfer\Command\Licence\DeletePeopleViaVariation;
use Dvsa\Olcs\Transfer\Command\Licence\UpdatePeople;
use Dvsa\Olcs\Transfer\Command\Application\UpdatePeople as UpdatePeopleApplication;
use Dvsa\Olcs\Transfer\Query\Licence\People;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Psr\Container\ContainerInterface;
use Laminas\Form\Form;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;

abstract class AbstractPeopleAdapter extends AbstractControllerAwareAdapter implements PeopleAdapterInterface
{
    public const ACTION_ADDED = 'A';

    public const ACTION_EXISTING = 'E';

    public const ACTION_CURRENT = 'C';

    public const ACTION_UPDATED = 'U';

    public const ACTION_DELETED = 'D';

    public const SOURCE_APPLICATION = 'A';

    public const SOURCE_ORGANISATION = 'O';

    protected array $tableData = [];

    private array $data;

    private array $licence;

    private $application = [];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function loadPeopleData($lva, $id): bool
    {
        if ($lva === AbstractController::LVA_LIC) {
            $this->loadPeopleDataForLicence($id);
        } else {
            $this->loadPeopleDataForApplication($id);
        }

        return true;
    }

    protected function loadPeopleDataForLicence($licenceId): void
    {
        $command = \Dvsa\Olcs\Transfer\Query\Licence\People::create(['id' => $licenceId]);

        $response = $this->handleQuery($command);
        if (!$response->isOk()) {
            throw new \RuntimeException('Failed to load people data');
        }

        $this->data = $this->licence = $response->getResult();
        $this->application = $this->data['application'];
    }

    protected function loadPeopleDataForApplication(int $applicationId): void
    {
        $command = \Dvsa\Olcs\Transfer\Query\Application\People::create(['id' => $applicationId]);

        $response = $this->handleQuery($command);
        if (!$response->isOk()) {
            throw new \RuntimeException('Failed to load people data');
        }

        $this->data = $this->application = $response->getResult();
        $this->licence = $this->data['licence'];
    }

    protected function handleQuery(\Dvsa\Olcs\Transfer\Query\QueryInterface $command): Response
    {
        return $this->container->get('ControllerPluginManager')->get(HandleQuery::class)->__invoke($command);
    }

    protected function handleCommand(\Dvsa\Olcs\Transfer\Command\CommandInterface $command): Response
    {
        $annotationBuilder = $this->container->get(AnnotationBuilder::class);
        $commandService = $this->container->get(CommandService::class);

        return $commandService->send($annotationBuilder->createCommand($command));
    }

    public function hasInforceLicences()
    {
        return $this->data['hasInforceLicences'];
    }

    public function isExceptionalOrganisation()
    {
        return $this->data['isExceptionalType'];
    }

    public function getOrganisation()
    {
        return $this->licence['organisation'] ?? null;
    }

    public function getOrganisationId()
    {
        return $this->licence['organisation']['id'];
    }

    public function getLicence(): ?array
    {
        return $this->licence;
    }

    public function getApplication(): ?array
    {
        return $this->application;
    }

    public function isSoleTrader()
    {
        return $this->data['isSoleTrader'];
    }

    public function isPartnership()
    {
        return $this->getOrganisationType() === \Common\RefData::ORG_TYPE_PARTNERSHIP;
    }

    public function hasMoreThanOneValidCurtailedOrSuspendedLicences()
    {
        return $this->data['hasMoreThanOneValidCurtailedOrSuspendedLicences'];
    }

    public function isOrganisationLimited(): bool
    {
        $limitedTypes = [
            \Common\RefData::ORG_TYPE_LLP,
            \Common\RefData::ORG_TYPE_RC,
        ];
        return in_array($this->getOrganisationType(), $limitedTypes, false);
    }

    public function isOrganisationOther(): bool
    {
        $types = [
            \Common\RefData::ORG_TYPE_OTHER,
        ];
        return in_array($this->getOrganisationType(), $types, false);
    }

    public function useDeltas(): bool
    {
        return (isset($this->data['useDeltas']) && $this->data['useDeltas']);
    }

    public function getPeople()
    {
        if ($this->getApplication()) {
            // need to merge the orgPeople with the appOrgPeople
            return $this->updateAndFilterTableData(
                $this->indexRows(self::SOURCE_ORGANISATION, $this->data['people']),
                $this->indexRows(self::SOURCE_APPLICATION, $this->data['application-people'])
            );
        }

        return $this->data['people'] ?? null;
    }

    /**
     * Get person data
     *
     * @param int $personId Person Id
     *
     * @return array|false person data or false if not found
     */
    public function getPersonData($personId)
    {
        foreach ($this->getPeople() as $organisationPerson) {
            if ($organisationPerson['person']['id'] == $personId) {
                return $organisationPerson;
            }
        }

        return false;
    }

    /**
     * Get first person data
     *
     * @return array|false person data or false if not found
     */
    public function getFirstPersonData()
    {
        return $this->getPeople()[0] ?? false;
    }

    /**
     * Abstract Method implementation
     *
     * @return void
     */
    #[\Override]
    public function addMessages()
    {
    }

    /**
     * Alter form for organisation
     *
     * @param Form         $form  form
     * @param TableBuilder $table table
     */
    #[\Override]
    public function alterFormForOrganisation(Form $form, $table): void
    {
        $labelTextForOrganisation = $this->getAddLabelTextForOrganisation();

        $action = $table->getAction('add');
        $table->removeAction('add');
        $action['label'] = $labelTextForOrganisation;
        $table->addAction('add', $action);
    }

    /**
     * Abstract method implementation
     *
     * @param Form $form form
     *
     * @return void
     */
    #[\Override]
    public function alterAddOrEditFormForOrganisation(Form $form)
    {
    }

    #[\Override]
    public function canModify(): bool
    {
        return true;
    }

    #[\Override]
    public function createTable()
    {
        /** @var TableBuilder $table */
        $table = $this->container
            ->get('Table')
            ->prepareTable($this->getTableConfig(), $this->getTableData());

        //  set empty message in depend of Organisation type
        if ($this->getOrganisationType() === RefData::ORG_TYPE_REGISTERED_COMPANY) {
            $table->setEmptyMessage('selfserve-app-subSection-your-business-people-ltd.table.empty-message');
        }

        return $table;
    }

    protected function getTableData()
    {
        if (empty($this->tableData)) {
            $this->tableData = $this->addNewStatuses(
                $this->formatTableData($this->getPeople())
            );
        }

        return $this->tableData;
    }

    private function addNewStatuses(array $tableData)
    {
        /** @var FlashMessenger $flashMessenger */
        $flashMessenger = $this->container->get('ControllerPluginManager')->get(FlashMessenger::class);
        $newPersonIDs = $flashMessenger->getMessages(AbstractController::FLASH_MESSENGER_CREATED_PERSON_NAMESPACE);

        $newTableData = [];

        foreach ($tableData as $key => $person) {
            $person['status'] = in_array($person['id'], $newPersonIDs) ? 'new' : null;

            $newTableData[$key] = $person;
        }

        return $newTableData;
    }

    protected function formatTableData($results)
    {
        $final = [];
        foreach ($results as $row) {
            // flatten the person's position if it's non null
            if (isset($row['position'])) {
                $row['person']['position'] = $row['position'];
            }

            // ... and action too
            if (isset($row['action'])) {
                $row['person']['action'] = $row['action'];
            }

            $final[] = $row['person'];
        }

        return $final;
    }

    public function getLicenceId()
    {
        return $this->getLicence()['id'];
    }

    public function getApplicationId()
    {
        return $this->getApplication()['id'];
    }

    public function getOrganisationType()
    {
        $orgData = $this->getOrganisation();
        return $orgData['type']['id'] ?? null;
    }

    public function getLicenceType()
    {
        if ($this->application !== null) {
            return $this->application['licenceType']['id'];
        }

        return $this->licence['licenceType']['id'];
    }

    #[\Override]
    public function delete($ids)
    {
        $response = $this->handleCommand($this->getDeleteCommand(['personIds' => $ids]));
        /* @var $response Response */
        if (!$response->isOk()) {
            throw new \RuntimeException('Error deleteing Org Person : ' . print_r($response->getResult(), true));
        }

        return true;
    }

    #[\Override]
    public function restore($ids)
    {
        // Can only restore in an application\variation
        $response = $this->handleCommand(
            \Dvsa\Olcs\Transfer\Command\Application\RestorePeople::create(
                ['id' => $this->getApplicationId(), 'personIds' => $ids]
            )
        );
        if (!$response->isOk()) {
            throw new \RuntimeException('Error restoring Person : ' . print_r($response->getResult(), true));
        }

        return true;
    }

    public function create(array $data)
    {
        $response = $this->handleCommand($this->getCreateCommand($data));
        if (!$response->isOk()) {
            throw new \RuntimeException('Error creating Person : ' . print_r($response->getResult(), true));
        }

        return true;
    }

    public function update(array $data)
    {
        $response = $this->handleCommand($this->getUpdateCommand($data));
        if (!$response->isOk()) {
            throw new \RuntimeException('Error updating Person : ' . print_r($response->getResult(), true));
        }

        return true;
    }

    protected function getCreateCommand($params)
    {
        $params['id'] = $this->getLicenceId();
        return CreatePeople::create($params);
    }

    protected function getUpdateCommand($params)
    {
        $params['person'] = $params['id'];
        $params['id'] = $this->getLicenceId();
        return UpdatePeople::create($params);
    }


    protected function getDeleteCommand($params)
    {
        $params['id'] = $this->getLicenceId();
        return DeletePeople::create($params);
    }

    protected function getTableConfig()
    {
        return 'lva-people';
    }

    private function updateAndFilterTableData($orgData, $applicationData)
    {
        $data = [];

        foreach ($orgData as $id => $row) {
            if (!isset($applicationData[$id])) {
                // E for existing (No updates)
                $row['action'] = self::ACTION_EXISTING;
                $data[] = $row;
            } elseif ($applicationData[$id]['action'] === self::ACTION_UPDATED) {
                $row['action'] = self::ACTION_CURRENT;
                $data[] = $row;
            }
        }

        return array_merge($data, $applicationData);
    }

    private function indexRows($key, $data)
    {
        $indexed = [];

        foreach ($data as $value) {
            // if we've got a link to an original person then that
            // trumps any other relation
            $id = $value['originalPerson']['id'] ?? $value['person']['id'];

            $value['person']['source'] = $key;
            $indexed[$id] = $value;
        }

        return $indexed;
    }

    public function getAddLabelTextForOrganisation()
    {
        $type = [
            RefData::ORG_TYPE_RC => 'lva.section.title.add_director',
            RefData::ORG_TYPE_LLP => 'lva.section.title.add_partner',
            RefData::ORG_TYPE_PARTNERSHIP => 'lva.section.title.add_partner',
            RefData::ORG_TYPE_OTHER => 'lva.section.title.add_person',
            RefData::ORG_TYPE_IRFO => 'lva.section.title.add_person'
        ];
        return $type[$this->getOrganisationType()] ?? null;
    }

    public function amendLicencePeopleListTable(TableBuilder $table)
    {
        $table->setSetting(
            'crud',
            [
                'actions' => [
                    'add' => [
                        'label' => $this->getAddLabelTextForOrganisation()
                    ]
                ]
            ]
        );
        return $table;
    }
}
