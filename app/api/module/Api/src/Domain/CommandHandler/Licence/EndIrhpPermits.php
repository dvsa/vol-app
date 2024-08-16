<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\IrhpApplication\Expire;
use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpApplicationsAndPermits as EndIrhpApplicationsAndPermitsCmd;
use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpPermits as EndIrhpPermitsCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\IrhpPermit\Terminate;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence;

/**
 * End IRHP permits relating to a licence
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EndIrhpPermits extends AbstractCommandHandler implements TransactionedInterface
{
    public const CONTEXT_TASK_DESCRIPTION_LOOKUP = [
        EndIrhpApplicationsAndPermitsCmd::CONTEXT_SURRENDER => 'licence surrendered',
        EndIrhpApplicationsAndPermitsCmd::CONTEXT_REVOKE => 'licence revoked',
        EndIrhpApplicationsAndPermitsCmd::CONTEXT_CNS => 'CNS processing of licence',
    ];

    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['IrhpPermit'];

    /**
     * Handle command
     *
     * @param EndIrhpPermitsCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $licenceId = $command->getId();
        $licence = $this->getRepo()->fetchById($licenceId);

        $permitsOrCertificatesEnded = false;

        $activeIrhpPermitsQuery = GetListByLicence::create(
            [
                'licence' => $command->getId(),
                'validOnly' => true
            ]
        );

        $activeIrhpPermits = $this->getRepo('IrhpPermit')->fetchList($activeIrhpPermitsQuery, Query::HYDRATE_OBJECT);

        if ($activeIrhpPermits->count()) {
            $permitsOrCertificatesEnded = true;

            foreach ($activeIrhpPermits as $irhpPermit) {
                $this->result->merge(
                    $this->handleSideEffect(
                        Terminate::create(
                            ['id' => $irhpPermit->getId()]
                        )
                    )
                );
            }
        }

        // Valid irhp applications are normally expired by the above terminate command, but only when the permit type
        // makes use of entries in the irhp permit table. Certificate of Roadworthiness doesn't make use the irhp
        // permit table so we need an additional step here to expire these applications

        $validIrhpApplications = $licence->getValidIrhpApplications();

        if ($validIrhpApplications->count()) {
            $permitsOrCertificatesEnded = true;

            foreach ($validIrhpApplications as $irhpApplication) {
                $this->result->merge(
                    $this->handleSideEffect(
                        Expire::create(
                            ['id' => $irhpApplication->getId()]
                        )
                    )
                );
            }
        }

        if ($permitsOrCertificatesEnded) {
            $createTaskCommand = $this->getCreateTaskCommand(
                $licenceId,
                $command->getContext()
            );

            $this->result->merge(
                $this->handleSideEffect($createTaskCommand)
            );
        }

        $this->result->addMessage('Cleared IRHP permits for licence ' . $licenceId);

        return $this->result;
    }

    /**
     * Return a CreateTask command relating to the ending of permits or certificates
     *
     * @param int $licenceId
     * @param string $context
     *
     * @return CreateTask
     */
    private function getCreateTaskCommand($licenceId, $context)
    {
        $description = sprintf(
            'Permits terminated after %s',
            self::CONTEXT_TASK_DESCRIPTION_LOOKUP[$context]
        );

        return CreateTask::create(
            [
                'category' => Category::CATEGORY_PERMITS,
                'subCategory' => Category::TASK_SUB_CATEGORY_PERMITS_GENERAL_TASK,
                'description' => $description,
                'licence' => $licenceId,
            ]
        );
    }
}
