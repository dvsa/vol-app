<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;
use Dvsa\Olcs\Api\Domain\Command\Email\SendPsvOperatorListReport as Command;

/**
 * Send  PSV OperatorList report
 */
final class SendPsvOperatorListReport extends AbstractCommandHandler implements EmailAwareInterface
{
    use \Dvsa\Olcs\Api\Domain\EmailAwareTrait;

    public const string EMAIL_TEMPLATE = 'report-psv-operator-list';
    public const string EMAIL_SUBJECT = 'email.notification.subject';

    /**
     * @var string
     */
    protected $repoServiceName = 'SystemParameter';

    /**
     * Handle Send PSV Operator List Report
     *
     * @param Command $command Command for sending PSV Report
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var SystemParameterRepo $systemParametersRepo */
        $systemParametersRepo = $this->getRepo();
        $email = $systemParametersRepo->fetchValue(SystemParameter::PSV_REPORT_EMAIL_LIST);

        if (is_null($email)) {
            throw new \InvalidArgumentException('No email address specified in system parameters for the PSV Report');
        }

        $message = new Message($email, self::EMAIL_SUBJECT);
        $message->setDocs([$command->getId()]);

        $this->sendEmailTemplate($message, self::EMAIL_TEMPLATE);

        $this->result->addId('document', $command->getId());
        $this->result->addMessage('PSV Operator list sent to: ' . $email);

        return $this->result;
    }
}
