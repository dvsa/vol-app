<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Lva;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Psr\Container\ContainerInterface;

/**
 * Class with common function to Save Company Subsidiary
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
abstract class AbstractCompanySubsidiary extends AbstractCommandHandler implements TransactionedInterface
{
    /** @var  \Dvsa\Olcs\Api\Domain\Repository\CompanySubsidiary */
    protected $repo;

    /**
     * Create subsidiary
     *
     * @param TransferCmd\Lva\AbstractCreateCompanySubsidiary $command   Command
     * @param int                                             $licenceId Assign to Licence
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    protected function create($command, $licenceId)
    {
        /** @var Entity\Licence\Licence $licence */
        $licence = $this->getRepo('Licence')->fetchById($licenceId);

        $companySubsidiary = new Entity\Organisation\CompanySubsidiary(
            $command->getName(),
            $command->getCompanyNo(),
            $licence
        );

        $this->repo->save($companySubsidiary);

        return (new Result())
            ->addId('companySubsidiary', $companySubsidiary->getId())
            ->addMessage('Company Subsidiary created');
    }

    /**
     * Update subsidiary
     *
     * @param TransferCmd\Lva\AbstractUpdateCompanySubsidiary $command Command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    protected function update($command)
    {
        $version = (int)$command->getVersion();

        /** @var Entity\Organisation\CompanySubsidiary $companySubsidiary */
        $companySubsidiary = $this->repo->fetchUsingId(
            $command,
            Query::HYDRATE_OBJECT,
            $version
        );

        //  update record data
        $companySubsidiary->setName($command->getName());
        $companySubsidiary->setCompanyNo($command->getCompanyNo());

        $this->repo->save($companySubsidiary);

        //  define was record changed and set related status and message
        $hasChanged = ($companySubsidiary->getVersion() !== $version);
        $message = ($hasChanged ? 'updated' : 'unchanged');

        return (new Result())
            ->setFlag('hasChanged', $hasChanged)
            ->addMessage('Company Subsidiary ' . $message);
    }

    /**
     * Delete subsidiary
     *
     * @param TransferCmd\Lva\AbstractDeleteCompanySubsidiary $command Command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    protected function delete($command)
    {
        /** @var Entity\Organisation\CompanySubsidiary[] $entities */
        $entities = $this->repo->fetchByIds($command->getIds());

        foreach ($entities as $entity) {
            $this->repo->delete($entity);
        }

        return (new Result())
            ->addMessage(count($command->getIds()) . ' Company Subsidiaries removed');
    }

    /**
     * Create task
     *
     * @param int    $licenceId Licence identifier
     * @param string $desc      Descripton
     *
     * @return Result
     */
    protected function createTask($licenceId, $desc)
    {
        return $this->handleSideEffect(
            DomainCmd\Task\CreateTask::create(
                [
                    'category' => Category::CATEGORY_APPLICATION,
                    'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_SUBSIDIARY_DIGITAL,
                    'description' => $desc,
                    'licence' => $licenceId,
                ]
            )
        );
    }

    /**
     * Update application completion
     *
     * @param int  $appId      Application identifier
     * @param bool $hasChanged Is there any changes
     *
     * @return Result
     */
    protected function updateApplicationCompetition($appId, $hasChanged = true)
    {
        return $this->handleSideEffect(
            DomainCmd\Application\UpdateApplicationCompletion::create(
                [
                    'id' => $appId,
                    'section' => 'businessDetails',
                    'data' => [
                        'hasChanged' => $hasChanged,
                    ],
                ]
            )
        );
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->extraRepos = array_merge(['CompanySubsidiary', 'Licence', 'Application'], $this->extraRepos);
        $instance = parent::__invoke($container, $requestedName, $options);
        $this->repo = $this->getRepo('CompanySubsidiary');
        return $instance;
    }
}
