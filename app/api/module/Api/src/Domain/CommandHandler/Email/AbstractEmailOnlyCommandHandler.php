<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

abstract class AbstractEmailOnlyCommandHandler extends AbstractCommandHandler implements EmailAwareInterface
{
    use EmailAwareTrait;

    /** @var Message */
    private $message;

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $this->message = new Message($command->getEmailAddress(), $this->getEmailSubject());
        $this->message->setTranslateToWelsh($command->shouldTranslateToWelsh());

        if (!empty($command->getDocs())) {
            $this->message->setDocs($command->getDocs());
        }

        $this->sendEmailTemplate($this->message, $this->getEmailTemplateName(), []);

        $result->addMessage('Email sent');

        return $result;
    }

    abstract protected function getEmailSubject(): string;

    abstract protected function getEmailTemplateName(): string;
}
