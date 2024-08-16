<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Scan;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\PrintScan\Scan;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create Separator Sheet
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateSeparatorSheet extends AbstractCommandHandler implements TransactionedInterface
{
    public const ERR_NO_ENTITY_FOR_CATEGORY = 'ERR_NO_ENTITY_FOR_CATEGORY';
    public const ERR_ENTITY_NAME_NOT_SETUP = 'ERR_ENTITY_NAME_NOT_SETUP';
    public const ERR_NO_DESCRIPTION = 'ERR_NO_DESCRIPTION';

    private static $formatDescNr = '%s (%d)';

    protected $repoServiceName = 'Scan';

    protected $extraRepos = [
        'Licence',
        'Bus',
        'BusRegSearchView',
        'Cases',
        'Category',
        'SubCategory',
        'SubCategoryDescription',
        'IrhpApplication'
    ];

    /**
     * Command handler
     *
     * @param \Dvsa\Olcs\Transfer\Command\Scan\CreateSeparatorSheet $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        $catId = $command->getCategoryId();
        $subCatId = $command->getSubCategoryId();
        $dateReceived = $command->getDateReceived();

        if (empty($command->getDescription()) && empty($command->getDescriptionId())) {
            throw new ValidationException(
                [self::ERR_NO_DESCRIPTION => 'Description or descriptionId must be specified']
            );
        }

        $entity = $this->getEntityForCategory($catId, $command->getEntityIdentifier());

        if ($command->getDescriptionId()) {
            $descriptionName = $this->getRepo('SubCategoryDescription')
                ->fetchById($command->getDescriptionId())
                ->getDescription();
        } else {
            $descriptionName = $command->getDescription();
        }

        $category = $this->getRepo()->getCategoryReference($catId);
        $subCategory = $this->getRepo()->getSubCategoryReference($subCatId);

        //  store scan
        $scan = new Scan();
        $scan->setCategory($category);
        $scan->setSubCategory($subCategory);
        $scan->setDescription($descriptionName);
        $scan->setDateReceived($dateReceived);

        $this->setScanProperties($catId, $scan, $entity);

        $this->getRepo()->save($scan);

        //  generate document
        $scanId = $scan->getId();

        $knownValues = [
            'DOC_CATEGORY_ID_SCAN'       => $catId,
            'DOC_CATEGORY_NAME_SCAN'     => sprintf(self::$formatDescNr, $category->getDescription(), $catId),
            'LICENCE_NUMBER_SCAN'        => $this->getLicNo($entity),
            'LICENCE_NUMBER_REPEAT_SCAN' => $this->getLicNo($entity),
            'ENTITY_ID_TYPE_SCAN'        => $this->getEntityTypeForCategory($catId),
            'ENTITY_ID_SCAN'             => $entity->getId(),
            'ENTITY_ID_REPEAT_SCAN'      => $entity->getId(),
            'DOC_SUBCATEGORY_ID_SCAN'    => $subCatId,
            'DOC_SUBCATEGORY_NAME_SCAN'  => sprintf(self::$formatDescNr, $subCategory->getSubCategoryName(), $subCatId),
            'DOC_DESCRIPTION_ID_SCAN'    => $scanId,
            'DOC_DESCRIPTION_NAME_SCAN'  => sprintf(self::$formatDescNr, $descriptionName, $scanId),
        ];
        $documentId = $this->generateDocument($knownValues);

        $this->result->merge(
            $this->handleSideEffect(
                Enqueue::create(
                    [
                        'documentId' => $documentId,
                        'jobName' => 'Scanning Separator Sheet',
                    ]
                )
            )
        );

        $this->result->addId('scan', $scan->getId());
        $this->result->addMessage('Scan ID ' . $scan->getId() . ' created');

        return $this->result;
    }

    /**
     * Set the applicable scan properties for a category
     *
     * @param int                                                            $categoryId Category id
     * @param \Dvsa\Olcs\Api\Entity\PrintScan\Scan                           $scan       Scan object
     * @param \Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface $entity     Entity
     *
     * @return void
     */
    protected function setScanProperties($categoryId, $scan, $entity)
    {
        // set associated entities dependent on category
        switch ($categoryId) {
            case Category::CATEGORY_APPLICATION:
            case Category::CATEGORY_LICENSING:
            case Category::CATEGORY_ENVIRONMENTAL:
                $scan->setLicence($entity);
                break;
            case Category::CATEGORY_COMPLIANCE:
                $scan->setCase($entity);
                $scan->setLicence($entity->getLicence());
                $scan->setTransportManager($entity->getTransportManager());
                break;
            case Category::CATEGORY_IRFO:
                $scan->setIrfoOrganisation($entity);
                break;
            case Category::CATEGORY_TRANSPORT_MANAGER:
                $scan->setTransportManager($entity);
                break;
            case Category::CATEGORY_BUS_REGISTRATION:
                $scan->setBusReg($entity);
                $scan->setLicence($entity->getLicence());
                break;
            case Category::CATEGORY_PERMITS:
                $scan->setIrhpApplication($entity);
                $scan->setLicence($entity->getLicence());
                break;
        }
    }

    /**
     * Get the entity that a category is related to
     *
     * @param int    $categoryId       Category identifier
     * @param string $entityIdentifier Entity identifier
     *
     * @return Entity\Application\Application
     *         | Entity\Cases\Cases
     *         | Entity\Licence\Licence
     *         | Entity\Tm\TransportManager
     *         | Entity\Organisation\Organisation
     *         | Entity\BusReg
     *         | Entity\Permits\IrhpApplication
     * @throws ValidationException
     */
    protected function getEntityForCategory($categoryId, $entityIdentifier)
    {
        switch ($categoryId) {
            case Category::CATEGORY_APPLICATION:
            case Category::CATEGORY_LICENSING:
            case Category::CATEGORY_ENVIRONMENTAL:
                return $this->getRepo('Licence')->fetchByLicNo($entityIdentifier);
            case Category::CATEGORY_COMPLIANCE:
                return $this->getRepo('Cases')->fetchById($entityIdentifier);
            case Category::CATEGORY_IRFO:
                return $this->getRepo()->getReference(Organisation::class, $entityIdentifier);
            case Category::CATEGORY_TRANSPORT_MANAGER:
                return $this->getRepo()->getReference(TransportManager::class, $entityIdentifier);
            case Category::CATEGORY_BUS_REGISTRATION:
                /* @var $busRegSearch \Dvsa\Olcs\Api\Entity\View\BusRegSearchView */
                $busRegSearch = $this->getRepo('BusRegSearchView')->fetchByRegNo($entityIdentifier);
                return $this->getRepo('Bus')->fetchById($busRegSearch->getId());
            case Category::CATEGORY_PERMITS:
                return $this->getIrhpApplicationByCombinedLicNoAndIrhpApplicationId($entityIdentifier);
            default:
                throw new ValidationException(
                    [self::ERR_NO_ENTITY_FOR_CATEGORY => 'Cannot get an entity for category Id ' . $categoryId]
                );
        }
    }

    /**
     * Get the Entity Name for a category ID
     *
     * @param int $categoryId
     *
     * @return string
     * @throws RuntimeException
     */
    protected function getEntityTypeForCategory($categoryId)
    {
        $names = [
            Category::CATEGORY_APPLICATION       => 'Licence',
            Category::CATEGORY_BUS_REGISTRATION  => 'Bus Route',
            Category::CATEGORY_COMPLIANCE        => 'Case',
            Category::CATEGORY_LICENSING         => 'Licence',
            Category::CATEGORY_ENVIRONMENTAL     => 'Licence',
            Category::CATEGORY_IRFO              => 'IRFO',
            Category::CATEGORY_TRANSPORT_MANAGER => 'Transport Manager',
            Category::CATEGORY_PERMITS           => 'IRHP Application'
        ];

        if (!isset($names[$categoryId])) {
            throw new ValidationException(
                [self::ERR_ENTITY_NAME_NOT_SETUP => 'Entity name is not setup for category Id ' . $categoryId]
            );
        }

        return $names[$categoryId];
    }

    /**
     * Get the Lic No associated with the entity or "Unknown" if no Lic No is associated
     *
     * @param $entity
     *
     * @return string Lic No or "Unknown"
     */
    protected function getLicNo($entity)
    {
        if (method_exists($entity, 'getLicNo')) {
            return $entity->getLicNo();
        }

        if (method_exists($entity, 'getLicence')) {
            return $entity->getLicence()->getLicNo();
        }

        return 'Unknown';
    }

    /**
     * Get an IRHP application by a identifier consisting of the licence number, followed by a forward slash,
     * followed by the IRHP application id
     *
     * @param string $entityIdentifier
     *
     * @return Entity\Permits\IrhpApplication
     *
     * @throws NotFoundException
     */
    protected function getIrhpApplicationByCombinedLicNoAndIrhpApplicationId($entityIdentifier)
    {
        $identifierElements = explode('/', $entityIdentifier);
        if (count($identifierElements) != 2) {
            throw new NotFoundException(
                'Identifier must contain a licence number, forward slash and IRHP application id'
            );
        }

        $licNo = trim($identifierElements[0]);
        $irhpApplicationId = trim($identifierElements[1]);

        $licence = $this->getRepo('Licence')->fetchByLicNo($licNo);
        $irhpApplication = $this->getRepo('IrhpApplication')->fetchById($irhpApplicationId);

        if ($irhpApplication->getLicence()->getId() !== $licence->getId()) {
            throw new NotFoundException(
                sprintf(
                    'IRHP application %s does not belong to licence %s',
                    $irhpApplication->getId(),
                    $licence->getLicNo()
                )
            );
        }

        return $irhpApplication;
    }

    protected function generateDocument($knownValues)
    {
        $dtoData = [
            'template' => 'Scanning_SeparatorSheet',
            'query' => [],
            'knownValues' => $knownValues,
            'description' => 'Scanning separator',
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_SCANNING_SEPARATOR,
            'isExternal' => false,
            'isScan' => false
        ];

        $result = $this->handleSideEffect(GenerateAndStore::create($dtoData));

        $this->result->merge($result);

        return $result->getId('document');
    }
}
