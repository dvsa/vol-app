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
                $nowDt = \DateTime::createFromInterface($now);

                // Atomically claim an attempt against the cap (race-safe); -1 = no slot left.
                $remaining = $this->getRepo('RetrievalOtp')->claimAttempt($otp->getId());

                if ($remaining >= 0 && $this->otpService->verify((string) $command->getCode(), (string) $otp->getCodeHash())) {
                    // Correct code — consume single-use atomically so a concurrent replay can't reuse it.
                    if ($this->getRepo('RetrievalOtp')->consume($otp->getId(), $nowDt)) {
                        $verified = true;
                        $grant = $this->sessionGrantService->issue((string) $link->getToken(), $now);
                        $this->recordRetrievalEvent($link, 'otp_succeeded', null, $ip);
                    } else {
                        // Lost the consume race (already used) — treat as a failure.
                        $this->recordRetrievalEvent($link, 'otp_failed', null, $ip);
                    }
                } else {
                    $attemptsRemaining = max(0, $remaining);
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
