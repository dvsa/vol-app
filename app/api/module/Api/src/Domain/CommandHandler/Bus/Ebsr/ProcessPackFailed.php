<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;

final class ProcessPackFailed extends AbstractProcessPack
{
    public function handleCommand(CommandInterface $command)
    {
        /** @var EbsrSubmissionEntity $ebsrSub */
        $ebsrSub = $this->getRepo('EbsrSubmission')->fetchUsingId($command);
        /** @var DocumentEntity $doc */
        $doc = $ebsrSub->getDocument();
        $this->processFailure(
            $ebsrSub,
            $doc,
            ['processing-failure' => 'an unexpected error occurred while processing submission'],
            '',
            []
        );

        return $this->result;
    }
}
