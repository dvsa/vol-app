<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Qandidate\Toggle\Context;

/**
 * Feature toggles with fixed answers, so booting the api does not read the feature_toggle table.
 *
 * The Address module asks for a toggle in onBootstrap, on every request and every CLI run.
 */
final class FixedToggleService extends ToggleService
{
    /**
     * @param list<string> $enabled names of the toggles that are on; every other toggle is off
     */
    public function __construct(private readonly array $enabled = [])
    {
        // Deliberately not calling the parent: there is no ToggleManager, and nothing here needs one
    }

    #[\Override]
    public function isEnabled(string $name, ?Context $context = null): bool
    {
        return in_array($name, $this->enabled, true);
    }

    #[\Override]
    public function isDisabled(string $name, ?Context $context = null): bool
    {
        return !$this->isEnabled($name, $context);
    }
}
