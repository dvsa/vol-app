<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkExpiredPermits as MarkExpiredPermitsCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Mark expired permits
 */
class MarkExpiredPermits extends AbstractCommandHandler
{
    public const MSG_CERT_EXPIRED = 'Roadworthiness certificate ID %d with MOT expiry %s has been expired';
    public const MSG_CERT_NOT_EXPIRED = 'Roadworthiness certificate ID %d with MOT expiry %s was not expired';
    public const MSG_CERT_NUM_EXPIRED = '%d certificates have been expired out of %d checked';

    protected $repoServiceName = 'IrhpPermit';

    protected $extraRepos = ['IrhpApplication'];

    /**
     * Handle command
     *
     * @param MarkExpiredPermitsCmd $command command
     *
     * @return Result
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleCommand(CommandInterface $command)
    {
        // mark all permits with validity date in the past as expired
        $this->getRepo('IrhpPermit')->markAsExpired();

        /** @var IrhpApplicationRepo $irhpApplicationRepo */
        $irhpApplicationRepo = $this->getRepo('IrhpApplication');

        // mark all IRHP applications without valid permits as expired (excludes certificates as they have no permits)
        $irhpApplicationRepo->markAsExpired();

        //expire roadworthiness certificates where the MOT has expired
        $validCertificates = $irhpApplicationRepo->fetchAllValidRoadworthiness();
        $expiredStatus = $this->refData(IrhpInterface::STATUS_EXPIRED);
        $numCertificatesExpired = 0;

        /** @var IrhpApplicationEntity $irhpApplication */
        foreach ($validCertificates as $irhpApplication) {
            $appId = $irhpApplication->getId();
            $expiry = $irhpApplication->getMotExpiryDate();

            try {
                $irhpApplication->expireCertificate($expiredStatus);
                $irhpApplicationRepo->save($irhpApplication);
                $message = sprintf(self::MSG_CERT_EXPIRED, $appId, $expiry);
                $numCertificatesExpired++;
            } catch (\Exception $e) {
                $message = sprintf(self::MSG_CERT_NOT_EXPIRED, $appId, $expiry) . ': ' . $e->getMessage();
            }

            $this->result->addMessage($message);
        }

        $this->result->addMessage(
            sprintf(self::MSG_CERT_NUM_EXPIRED, $numCertificatesExpired, count($validCertificates))
        );

        $this->result->addMessage('Expired permits have been marked');

        return $this->result;
    }
}
