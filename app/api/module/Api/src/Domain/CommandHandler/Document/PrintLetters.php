<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

class PrintLetters extends AbstractCommandHandler
{
    public function handleCommand(CommandInterface $command)
    {
        $method = $command->getMethod();
        foreach ($command->getIds() as $documentId) {
            $this->result->merge(
                $this->handleSideEffect(
                    \Dvsa\Olcs\Transfer\Command\Document\PrintLetter::create(
                        [
                            'id' => $documentId,
                            'method' => $method
                        ]
                    )
                )
            );
        }

        return $this->result;
    }
}
