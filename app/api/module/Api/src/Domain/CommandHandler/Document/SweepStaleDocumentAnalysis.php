<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command\Document\SweepStaleDocumentAnalysis as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Olcs\Logging\Log\Logger;

/**
 * Resolves stale PENDING analyses to TIMEOUT - the backstop that stops lost work going
 * unnoticed.
 *
 * Stale rows are read before the update purely so the log names them; the read is not part of
 * the write path, and a row that completes in between is simply not swept.
 *
 * The threshold must stay above the analysis timeout plus result-processing latency, or it
 * produces false TIMEOUTs.
 */
final class SweepStaleDocumentAnalysis extends AbstractCommandHandler implements ConfigAwareInterface
{
    use ConfigAwareTrait;

    public const DEFAULT_THRESHOLD_MINUTES = 60;

    protected $repoServiceName = Repository\DocumentAnalysis::class;

    /**
     * @param Cmd $command
     */
    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        if (!$command instanceof Cmd) {
            throw new RuntimeException(sprintf('%s cannot handle %s', static::class, $command::class));
        }

        $thresholdMinutes = $command->getThresholdMinutes() ?? $this->getConfiguredThresholdMinutes();
        $threshold = new \DateTimeImmutable(sprintf('-%d minutes', $thresholdMinutes));

        /** @var Repository\DocumentAnalysis $repo */
        $repo = $this->getRepo();

        foreach ($repo->fetchStalePending($threshold) as $analysis) {
            Logger::warn(
                'IDP analysis timed out; no result received within threshold',
                [
                    'analysis_token' => $analysis->getTokenString(),
                    'application_id' => $analysis->getApplication()?->getId(),
                    'document_id' => $analysis->getDocument()?->getId(),
                    'created_on' => $analysis->getCreatedOn(true)?->format('c'),
                    'threshold_minutes' => $thresholdMinutes,
                ]
            );
        }

        $swept = $repo->sweepStalePending($threshold);

        $this->result->addMessage(
            sprintf('Swept %d stale document analysis row(s) to TIMEOUT (threshold %d minutes)', $swept, $thresholdMinutes)
        );

        return $this->result;
    }

    private function getConfiguredThresholdMinutes(): int
    {
        $configured = (int)($this->getConfig()['idp']['sweeper_threshold_minutes'] ?? self::DEFAULT_THRESHOLD_MINUTES);

        return $configured > 0 ? $configured : self::DEFAULT_THRESHOLD_MINUTES;
    }
}
