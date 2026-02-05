<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;
use Dvsa\Olcs\Api\Domain\Command\Email\SendInternationalGoods as SendInternationalGoodsCmd;

/**
 * Send international goods report
 */
final class SendInternationalGoods extends AbstractCommandHandler implements EmailAwareInterface
{
    use EmailAwareTrait;

    public const string EMAIL_TEMPLATE = 'report-international-goods';
    public const string EMAIL_SUBJECT = 'email.notification.subject';

    /**
     * @var string
     */
    protected $repoServiceName = 'SystemParameter';

    /**
     * Email the international goods report
     *
     * @param SendInternationalGoodsCmd|CommandInterface $command send international goods command
     *
     * @throws \InvalidArgumentException
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var SystemParameterRepo $repo */
        $repo = $this->getRepo();
        $email = $repo->fetchValue(SystemParameter::INTERNATIONAL_GV_REPORT_EMAIL_TO);

        if (!isset($email)) {
            throw new \InvalidArgumentException('No email specified for international GV report');
        }

        //this will give us a comma separated string, the email service expects an array
        $ccList = $repo->fetchValue(SystemParameter::INTERNATIONAL_GV_REPORT_EMAIL_CC);

        $message = new Message($email, self::EMAIL_SUBJECT);
        $message->setDocs([$command->getId()]);
        $message->setCcFromString($ccList);

        $this->sendEmailTemplate($message, self::EMAIL_TEMPLATE);

        $this->result->addId('document', $command->getId());
        $this->result->addMessage('International goods sent to: ' . $email . ' and CC to ' . $ccList);

        return $this->result;
    }
}
