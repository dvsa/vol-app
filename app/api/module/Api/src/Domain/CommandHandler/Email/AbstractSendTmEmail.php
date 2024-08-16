<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\SendTmApplication as Cmd;

abstract class AbstractSendTmEmail extends AbstractCommandHandler implements \Dvsa\Olcs\Api\Domain\EmailAwareInterface
{
    use \Dvsa\Olcs\Api\Domain\EmailAwareTrait;

    protected $repoServiceName = 'TransportManagerApplication';

    protected $template;

    protected $subject;

    /**
     * @param Cmd $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $tma \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication */
        $tma = $this->getRepo()->fetchUsingId($command);

        if (!$tma->getTransportManager()->getUsers()->isEmpty()) {
            // got user linked to the TM
            $user = $tma->getTransportManager()->getUsers()->first();
            $username = $user->getLoginId();

            // use user's preference
            $translateToWelsh = $user->getTranslateToWelsh();
        } else {
            // no user linked
            $username = 'not registered';

            // use licence settings
            $translateToWelsh = $tma->getApplication()->getLicence()->getTranslateToWelsh();
        }

        $message = new \Dvsa\Olcs\Email\Data\Message(
            $tma->getTransportManager()->getHomeCd()->getEmailAddress(),
            $this->subject
        );
        $message->setTranslateToWelsh($translateToWelsh);

        $this->sendEmailTemplate(
            $message,
            $this->template,
            [
                'organisation' => $tma->getApplication()->getLicence()->getOrganisation()->getName(),
                'reference' => $tma->getApplication()->getLicence()->getLicNo() . '/' . $tma->getApplication()->getId(),
                'username' => $username,
                'isNi' => $tma->getApplication()->getNiFlag() === 'Y',
                'signInLink' => sprintf(
                    'http://selfserve/%s/%d/transport-managers/details/%d/edit-details/',
                    ($tma->getApplication()->getIsVariation()) ? 'variation' : 'application',
                    $tma->getApplication()->getId(),
                    $tma->getId()
                )
            ]
        );

        $result = new Result();
        $result->addId('transportManagerApplication', $tma->getId());
        $result->addMessage('Transport Manager Application email sent');
        return $result;
    }
}
