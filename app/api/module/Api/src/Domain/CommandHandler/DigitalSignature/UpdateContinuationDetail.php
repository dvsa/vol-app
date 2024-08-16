<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DigitalSignature;

use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateContinuationDetail as UpdateContinuationDetailCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail as ContinuationDetailRepo;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

final class UpdateContinuationDetail extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ContinuationDetail';

    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof UpdateContinuationDetailCmd);

        $repo = $this->getRepo('ContinuationDetail');
        assert($repo instanceof ContinuationDetailRepo);

        $continuationDetailId = $command->getContinuationDetail();
        $continuationDetail = $repo->fetchById($continuationDetailId);

        assert($continuationDetail instanceof ContinuationDetailEntity);

        $digitalSignature = $repo->getReference(DigitalSignature::class, $command->getDigitalSignature());

        $continuationDetail->updateDigitalSignature(
            $repo->getRefdataReference(RefData::SIG_DIGITAL_SIGNATURE),
            $digitalSignature
        );

        $repo->save($continuationDetail);
        $this->result->addMessage('Digital signature added to continuationDetail ' . $continuationDetailId);

        return $this->result;
    }
}
