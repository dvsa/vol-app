<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepository;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

final class AgreeTerms extends AbstractCommandHandler implements AuthAwareInterface, CacheAwareInterface
{
    use AuthAwareTrait;
    use CacheAwareTrait;

    public const string SUCCESS_MSG = 'Terms and conditions accepted';

    protected $repoServiceName = 'User';

    public function handleCommand(CommandInterface $command)
    {
        $userId = $this->getCurrentUser()->getId();

        /**
         * @var UserRepository $repo
         * @var User           $user
         */
        $repo = $this->getRepo();

        $user = $repo->fetchById($userId);
        $user->agreeTermsAndConditions();
        $repo->save($user);

        $this->clearUserCaches([$userId]);

        $this->result->addId('User', $userId);
        $this->result->addMessage(self::SUCCESS_MSG);

        return $this->result;
    }
}
