<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareInterface;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareTrait;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\StaticBookmark;

/**
 * Emits the page margins for the Gotenberg disc base templates.
 *
 * The *DiscTemplateGotenberg templates carry this bookmark in place of the
 * literal \margt/\margl control words, so the whole-page position of a disc
 * run can be nudged at runtime via SystemParameter (e.g. to absorb a
 * printer's feed offset against the pre-printed stationery) without
 * re-uploading the template. Values are twips; +57 moves content ~1mm
 * down/right. Falls back to the scan-calibrated defaults when the
 * SystemParameter rows are missing or non-numeric.
 */
abstract class AbstractDiscMargins extends StaticBookmark implements RepositoryManagerAwareInterface
{
    use RepositoryManagerAwareTrait;

    /** Content is raw RTF control words - the parser must not rewrite it */
    public const PREFORMATTED = true;

    public const TOP_PARAM = '';
    public const LEFT_PARAM = '';
    public const DEFAULT_TOP = 0;
    public const DEFAULT_LEFT = 0;

    #[\Override]
    public function render()
    {
        return sprintf(
            '\margt%d\margl%d',
            $this->resolve(static::TOP_PARAM, static::DEFAULT_TOP),
            $this->resolve(static::LEFT_PARAM, static::DEFAULT_LEFT)
        );
    }

    private function resolve(string $param, int $default): int
    {
        if ($param === '') {
            throw new \LogicException(static::class . ' must override TOP_PARAM and LEFT_PARAM');
        }

        $repo = $this->getRepoManager()?->get('SystemParameter');

        return $repo?->fetchNumericValue($param, $default) ?? $default;
    }
}
