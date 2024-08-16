<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Surrender;

use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Surrender\Create as Cmd;
use Dvsa\Olcs\Api\Entity\Surrender;

final class Create extends AbstractSurrenderCommandHandler
{
    protected $extraRepos = ['Licence'];

    /**
     * @param Cmd $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\ForbiddenException
     */
    public function handleCommand(CommandInterface $command)
    {
        try {
            $surrender = $this->getSurrender($command->getId());
            if ($surrender->getStatus()->getId() !== RefData::SURRENDER_STATUS_WITHDRAWN) {
                throw new ForbiddenException('A surrender record already exists for this licence and isn\'t withdrawn');
            }
            $this->withdrawnSurrender($surrender);
        } catch (NotFoundException) {
            $this->newSurrender($command);
        } catch (ForbiddenException $exception) {
            throw $exception;
        }

        return $this->result;
    }

    /**
     * @param int $licenceId
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    protected function newSurrender(CommandInterface $command): void
    {
        $licence = $this->getRepo('Licence')->fetchById($command->getId());
        $status = $this->getRepo()->getRefdataReference($command->getStatus());

        $surrender = new Surrender();
        $surrender->setLicence($licence);
        $surrender->setStatus($status);

        $this->getRepo()->save($surrender);

        $this->result->addId('surrender', $surrender->getId());
        $this->result->addMessage('Surrender successfully created.');
    }

    protected function withdrawnSurrender(Surrender $surrender)
    {
        $status = $this->getRepo()->getRefdataReference(RefData::SURRENDER_STATUS_START);

        $surrender->setStatus($status);
        $this->getRepo()->save($surrender);

        $this->result->addId('surrender', $surrender->getId());
        $this->result->addMessage('Surrender successfully restarted after being withdrawn.');
    }
}
