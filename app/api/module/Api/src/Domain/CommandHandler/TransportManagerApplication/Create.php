<?php

/**
 * Create a Transport Manager Application for a User
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\Create as CreateTmApplicationCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Psr\Container\ContainerInterface;

/**
 * Create a Transport Manager Application
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Create extends AbstractCommandHandler implements
    \Dvsa\Olcs\Api\Domain\AuthAwareInterface,
    \Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface
{
    use \Dvsa\Olcs\Api\Domain\AuthAwareTrait;
    use QueueAwareTrait;

    protected $repoServiceName = 'TransportManagerApplication';
    /**
     * @var \Dvsa\Olcs\Api\Domain\Repository\User
     */
    protected $userRepo;
    /**
     * @var \Dvsa\Olcs\Api\Domain\Repository\TransportManager
     */
    protected $tmRepo;

    /**
     * Handle command
     *
     * @param CommandInterface|CreateTmApplicationCmd $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /* @var $user UserEntity */
        $user = $this->userRepo->fetchForTma($command->getUser());
        $this->validateTransportManagerApplication($command->getApplication(), $user);

        if (!$user->getTransportManager()) {
            // create a Transport Manager
            $transportManager = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
            $transportManager->setHomeCd($user->getContactDetails());
            $transportManager->setTmStatus(
                $this->getRepo()->getRefdataReference(ContactDetails::TRANSPORT_MANAGER_STATUS_CURRENT)
            );
            $this->tmRepo->save($transportManager);

            $transportManager->addUsers($user);
            $user->setTransportManager($transportManager);
            $this->userRepo->save($user);

            $result->merge($this->handleSideEffect($this->nysiisQueueCmd($transportManager->getId())));
        }

        if ($command->getDob()) {
            $user->getContactDetails()->getPerson()->setBirthDate(new DateTime($command->getDob()));
        }

        $tma = new TransportManagerApplication();
        $tma->setAction($command->getAction());
        $tma->setApplication($this->getRepo()->getReference(Application::class, $command->getApplication()));
        $tma->setTmApplicationStatus(
            $this->getRepo()->getRefdataReference(TransportManagerApplication::STATUS_INCOMPLETE)
        );
        $tma->setTransportManager($user->getTransportManager());

        $this->getRepo()->save($tma);

        if ($this->getUser() !== $user) {
            $result->merge(
                $this->handleSideEffect(
                    \Dvsa\Olcs\Transfer\Command\TransportManagerApplication\SendTmApplication::create(
                        [
                            'id' => $tma->getId()
                        ]
                    )
                )
            );
        }

        $result->merge(
            $this->handleSideEffect(
                \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::create(
                    ['id' => $tma->getApplication()->getId(), 'section' => 'transportManagers']
                )
            )
        );

        $result->addId('transportManagerApplication', $tma->getId());
        $result->addMessage('Transport Manager successfully created.');

        return $result;
    }

    /**
     * Validates transport manager application
     *
     * @param int        $applicationId application id
     * @param UserEntity $user          user entity
     *
     * @throws ValidationException
     *
     * @return void
     */
    protected function validateTransportManagerApplication($applicationId, UserEntity $user)
    {
        if (!$user->getTransportManager()) {
            return;
        }
        $tmId = $user->getTransportManager()->getId();
        $tmApps = $this->getRepo()->fetchByTmAndApplication($tmId, $applicationId, true);

        if ($tmApps) {
            throw new ValidationException(
                [
                    'registeredUser' => [
                        TransportManagerApplication::ERROR_TM_EXIST =>
                            $user->getContactDetails()->getPerson()->getForename() . ' ' .
                            $user->getContactDetails()->getPerson()->getFamilyname() .
                            ' has already been added to this application'
                    ]
                ]
            );
        }
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->userRepo = $container->get('RepositoryServiceManager')->get('User');
        $this->tmRepo = $container->get('RepositoryServiceManager')->get('TransportManager');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
