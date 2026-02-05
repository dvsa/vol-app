<?php

/**
 * Send Continuation Not Sought Email
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Api\Domain\TranslatorAwareInterface;
use Dvsa\Olcs\Api\Domain\TranslatorAwareTrait;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Email\Data\Message as EmailMessage;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Send Continuation Not Sought Email
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class SendContinuationNotSought extends AbstractCommandHandler implements
    EmailAwareInterface,
    TranslatorAwareInterface
{
    use EmailAwareTrait;
    use TranslatorAwareTrait;

    protected $repoServiceName = 'SystemParameter';

    public const string DATE_FORMAT = 'd/m/Y';

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $licences array \Dvsa\Olcs\Api\Entity\Licence\Licence */
        $licences = $command->getLicences();

        /* @var $endDate \DateTime */
        $endDate = $command->getDate();
        $startDate = clone $endDate;
        $startDate->sub(new \DateInterval('P1M')); // -1 month

        $to = $this->getRepo('SystemParameter')->fetchValue(SystemParameter::CNS_EMAIL_LIST);
        $cc = $this->getRepo('SystemParameter')->fetchValue(SystemParameter::CNS_EMAIL_LIST_CC);

        $subject = $this->translate('email.cns.subject');
        $message = new EmailMessage($to, $subject);
        $message->setCcFromString($cc);
        $message->setSubjectVariables(
            [
                $startDate->format(self::DATE_FORMAT),
                $endDate->format(self::DATE_FORMAT),
            ]
        );

        $this->result->merge(
            $this->sendEmailTemplate(
                $message,
                'continuation-not-sought',
                [
                    'startDate' => $startDate->format(self::DATE_FORMAT),
                    'endDate' => $endDate->format(self::DATE_FORMAT),
                    'licences' => $licences,
                ]
            )
        );

        $this->result->addMessage('Continuation Not Sought email sent to: ' . $to . ' and CC to ' . $cc);

        return $this->result;
    }
}
