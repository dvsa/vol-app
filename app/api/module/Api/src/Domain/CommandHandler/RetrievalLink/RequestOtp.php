<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\RetrievalLink;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Api\Domain\Retrieval\RetrievalLinkAccessTrait;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink as RetrievalLinkEntity;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalOtp as RetrievalOtpEntity;
use Dvsa\Olcs\Api\Service\Retrieval\OtpService;
use Dvsa\Olcs\Api\Service\Retrieval\RetrievalPolicy;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

/**
 * Issues a one-time code for an OTP-gated link and emails it to the link's original recipient.
 *
 * Responds identically ("if eligible, a code has been sent") whether or not the link exists, is
 * usable, or is OTP-gated — so the endpoint is not an existence oracle and cannot be abused to
 * probe links. Send rate is capped per link to stop it being used as an email cannon. The newest
 * code supersedes any earlier active code.
 */
final class RequestOtp extends AbstractCommandHandler implements EmailAwareInterface
{
    use EmailAwareTrait;
    use RetrievalLinkAccessTrait;

    public const EMAIL_TEMPLATE = 'retrieval-otp';
    public const EMAIL_SUBJECT = 'Your document security code';

    /** At most this many codes may be requested per link per window. */
    private const MAX_REQUESTS_PER_WINDOW = 5;
    private const RATE_WINDOW_SECONDS = 3600;

    protected $repoServiceName = 'RetrievalLink';

    protected $extraRepos = ['RetrievalOtp', 'RetrievalLinkEvent'];

    private OtpService $otpService;

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        $now = new \DateTimeImmutable();
        $ip = $command->getIp();

        /** @var RetrievalLinkEntity|null $link */
        $link = $this->getRepo()->fetchByToken((string) $command->getToken());

        if ($this->isLinkUsable($link, $now) && $link->getGateMode() === RetrievalPolicy::GATE_OTP) {
            if ($this->withinRateLimit($link, $now)) {
                $this->issueCode($link, $now, $ip);
            } else {
                $this->recordRetrievalEvent($link, 'denied', null, $ip, null, 'otp rate limit');
            }
        }

        // Deliberately generic — never reveals whether a code was actually sent.
        $this->result->addMessage('If this link requires a security code, one has been sent to the recipient.');

        return $this->result;
    }

    private function withinRateLimit(RetrievalLinkEntity $link, \DateTimeImmutable $now): bool
    {
        $since = \DateTime::createFromInterface($now->sub(new \DateInterval('PT' . self::RATE_WINDOW_SECONDS . 'S')));

        return $this->getRepo('RetrievalOtp')->countRequestsSince($link->getId(), $since) < self::MAX_REQUESTS_PER_WINDOW;
    }

    private function issueCode(RetrievalLinkEntity $link, \DateTimeImmutable $now, ?string $ip): void
    {
        $otpRepo = $this->getRepo('RetrievalOtp');
        $otpRepo->invalidateActiveForLink($link->getId(), \DateTime::createFromInterface($now));

        $code = $this->otpService->generateCode();

        $otp = new RetrievalOtpEntity();
        $otp->setRetrievalLink($link);
        $otp->setCodeHash($this->otpService->hash($code));
        $otp->setExpiresAt(\DateTime::createFromInterface($this->otpService->expiryFrom($now)));
        $otp->setMaxAttempts(OtpService::MAX_ATTEMPTS);
        $otp->setRequestIp($ip);
        $otpRepo->save($otp);

        $this->sendCode($link, $code);
        $this->recordRetrievalEvent($link, 'otp_requested', null, $ip);
    }

    private function sendCode(RetrievalLinkEntity $link, string $code): void
    {
        $message = new Message((string) $link->getRecipientEmail(), self::EMAIL_SUBJECT);
        $this->sendEmailTemplate($message, self::EMAIL_TEMPLATE, [
            'code' => $code,
            'expiryMinutes' => intdiv(OtpService::TTL_SECONDS, 60),
        ]);
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->otpService = $container->get(OtpService::class);

        return parent::__invoke($container, $requestedName, $options);
    }
}
