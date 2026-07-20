<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\RetrievalLink;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Retrieval\RetrievalLinkAccessTrait;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink as RetrievalLinkEntity;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalOtp as RetrievalOtpEntity;
use Dvsa\Olcs\Api\Service\Retrieval\OtpService;
use Dvsa\Olcs\Api\Service\Retrieval\RetrievalPolicy;
use Dvsa\Olcs\Api\Service\Retrieval\SessionGrantService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

/**
 * Verifies a submitted one-time code. On success, consumes the code and issues a short-lived
 * session grant (bound to this link's token) that unlocks downloads. Enforces the per-code
 * attempt cap — the code is retired once exhausted, forcing the recipient to request a new one.
 *
 * Returns via result flags: `verified` (bool), `grant` (string|null), `attemptsRemaining` (int).
 */
final class VerifyOtp extends AbstractCommandHandler
{
    use RetrievalLinkAccessTrait;

    protected $repoServiceName = 'RetrievalLink';

    protected $extraRepos = ['RetrievalOtp', 'RetrievalLinkEvent'];

    private OtpService $otpService;

    private SessionGrantService $sessionGrantService;

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        $now = new \DateTimeImmutable();
        $ip = $command->getIp();

        $verified = false;
        $grant = null;
        $attemptsRemaining = 0;

        /** @var RetrievalLinkEntity|null $link */
        $link = $this->getRepo()->fetchByToken((string) $command->getToken());

        if ($this->isLinkUsable($link, $now) && $link->getGateMode() === RetrievalPolicy::GATE_OTP) {
            /** @var RetrievalOtpEntity|null $otp */
            $otp = $this->getRepo('RetrievalOtp')->fetchLatestActive($link->getId(), \DateTime::createFromInterface($now));

            if ($otp !== null) {
                $attempts = $otp->getAttempts() + 1;
                $otp->setAttempts($attempts);

                if ($this->otpService->verify((string) $command->getCode(), (string) $otp->getCodeHash())) {
                    $otp->setConsumedAt(\DateTime::createFromInterface($now));
                    $this->getRepo('RetrievalOtp')->save($otp);

                    $verified = true;
                    $grant = $this->sessionGrantService->issue((string) $link->getToken(), $now);
                    $this->recordRetrievalEvent($link, 'otp_succeeded', null, $ip);
                } else {
                    if ($attempts >= $otp->getMaxAttempts()) {
                        // Exhausted — retire the code so a fresh one must be requested.
                        $otp->setInvalidatedAt(\DateTime::createFromInterface($now));
                    }
                    $this->getRepo('RetrievalOtp')->save($otp);

                    $attemptsRemaining = max(0, $otp->getMaxAttempts() - $attempts);
                    $this->recordRetrievalEvent($link, 'otp_failed', null, $ip);
                }
            }
        }

        $this->result->setFlag('verified', $verified);
        $this->result->setFlag('grant', $grant);
        $this->result->setFlag('attemptsRemaining', $attemptsRemaining);
        $this->result->addMessage($verified ? 'Security code accepted' : 'Incorrect or expired security code');

        return $this->result;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->otpService = $container->get(OtpService::class);
        $this->sessionGrantService = $container->get(SessionGrantService::class);

        return parent::__invoke($container, $requestedName, $options);
    }
}
