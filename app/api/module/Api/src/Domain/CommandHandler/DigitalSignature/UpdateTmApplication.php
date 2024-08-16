<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DigitalSignature;

use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateTmApplication as UpdateTmApplicationCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TmApplicationRepo;
use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication as TmApplicationEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\Submit as SubmitApplicationCmd;

final class UpdateTmApplication extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TransportManagerApplication';

    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof UpdateTmApplicationCmd);

        $repo = $this->getRepo('TransportManagerApplication');
        assert($repo instanceof TmApplicationRepo);

        $tmApplicationId = $command->getApplication();
        $tmApplication = $repo->fetchById($tmApplicationId);

        assert($tmApplication instanceof TmApplicationEntity);

        $signatureType = $repo->getRefdataReference(RefData::SIG_DIGITAL_SIGNATURE);
        $digitalSignature = $repo->getReference(DigitalSignature::class, $command->getDigitalSignature());
        $role = $command->getRole();

        switch ($role) {
            case RefData::TMA_SIGN_AS_TM:
                $nextStatus = TmApplicationEntity::STATUS_TM_SIGNED;
                $tmApplication->updateTmDigitalSignature($signatureType, $digitalSignature);
                break;
            case RefData::TMA_SIGN_AS_OP:
                $nextStatus = TmApplicationEntity::STATUS_RECEIVED;
                $tmApplication->updateOperatorDigitalSignature($signatureType, $digitalSignature);
                break;
            case RefData::TMA_SIGN_AS_TM_OP:
                $nextStatus = TmApplicationEntity::STATUS_RECEIVED;
                $tmApplication->updateOperatorDigitalSignature($signatureType, $digitalSignature);
                $tmApplication->updateTmDigitalSignature($signatureType, $digitalSignature);
                break;
            default:
                throw new \Exception('Tm Role is not matched');
        }

        $repo->save($tmApplication);

        $this->result->addMessage('Digital signature added to TM application ' . $tmApplicationId);

        $this->result->merge(
            $this->handleSideEffect(
                SubmitApplicationCmd::create(
                    ['id' => $tmApplicationId, 'nextStatus' => $nextStatus]
                )
            )
        );

        return $this->result;
    }
}
