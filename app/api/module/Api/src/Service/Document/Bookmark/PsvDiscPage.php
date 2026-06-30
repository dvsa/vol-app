<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PsvDiscBundle as Qry;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;

/**
 * PSV Disc Page bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class PsvDiscPage extends AbstractDiscList
{
    /**
     * Discs per row in a page
     */
    public const PER_ROW = 6;

    /**
     * Bookmark variable prefix
     */
    public const BOOKMARK_PREFIX = 'PSV';

    /**
     * Short version of standard placeholder
     * @see https://jira.i-env.net/browse/OLCS-5988
     */
    public const SHORT_PLACEHOLDER = 'XXXXXX';

    public const QUERY_CLASS = Qry::class;

    /**
     * Stationery alignment defaults (twips). Operators may override at runtime
     * via SystemParameter rows of the same name; the snippet substitutes them
     * into table row heights and paragraph line spacing so PSV 423 alignment
     * can be tuned without a redeploy. Defaults assume evenly spaced disc
     * circles down the sheet (3 × 89mm rows totalling 267mm on a 268mm page
     * content area). Line spacing 240 twips = 12pt, fixed leading.
     */
    public const DEFAULT_ALIGNMENT = [
        SystemParameter::PSV_DISC_ROW_HEIGHT_1 => 5040,
        SystemParameter::PSV_DISC_ROW_HEIGHT_2 => 5040,
        SystemParameter::PSV_DISC_ROW_HEIGHT_3 => 5040,
        SystemParameter::PSV_DISC_LINE_SPACING => 240,
    ];

    protected $discBundle = [
        'licence' => [
            'organisation'
        ]
    ];

    #[\Override]
    public function render()
    {
        if (empty($this->data)) {
            return '';
        }

        foreach ($this->data as $key => $disc) {
            $licence = $disc['licence'];
            $organisation = $licence['organisation'];

            // split the org over multiple lines if necessary
            $orgParts = $this->splitString($organisation['name']);

            $prefix = $this->getPrefix($key);

            $discs[] = [
                $prefix . 'TITLE'       => $disc['isCopy'] === 'Y' ? 'COPY' : '',
                $prefix . 'DISC_NO'     => $disc['discNo'],
                $prefix . 'LINE1'       => $orgParts[0] ?? '',
                $prefix . 'LINE2'       => $orgParts[1] ?? '',
                $prefix . 'LINE3'       => $orgParts[2] ?? '',
                $prefix . 'LICENCE'     => $licence['licNo'],
                $prefix . 'VALID_DATE'  => isset($licence['inForceDate'])
                    ? $this->formatDate($licence['inForceDate'])
                    : 'N/A',
                $prefix . 'EXPIRY_DATE' => isset($licence['expiryDate'])
                    ? $this->formatDate($licence['expiryDate'])
                    : 'N/A'
            ];
        }

        /**
         * We always want a full page of discs, even if we have to
         * fill the rest up with placeholders
         */
        while (($length = count($discs) % self::PER_PAGE) !== 0) {
            $prefix = $this->getPrefix($length);
            $discs[] = [
                $prefix . 'TITLE'       => self::PLACEHOLDER,
                $prefix . 'DISC_NO'     => self::SHORT_PLACEHOLDER,
                $prefix . 'LINE1'       => self::PLACEHOLDER,
                $prefix . 'LINE2'       => self::PLACEHOLDER,
                $prefix . 'LINE3'       => self::PLACEHOLDER,
                $prefix . 'LICENCE'     => self::PLACEHOLDER,
                $prefix . 'VALID_DATE'  => self::PLACEHOLDER,
                $prefix . 'EXPIRY_DATE' => self::PLACEHOLDER
            ];
        }

        $alignment = $this->isPinnedLayout() ? $this->resolveAlignment() : [];

        // bit ugly, but now we have to chunk the discs into N per page
        $discGroups = [];
        for ($i = 0; $i < count($discs); $i += self::PER_PAGE) {
            $pageDiscs = $alignment;
            for ($j = 0; $j < self::PER_PAGE; $j++) {
                $pageDiscs = array_merge(
                    $pageDiscs,
                    $discs[$i + $j]
                );
            }
            $discGroups[] = $pageDiscs;
        }

        return $this->renderSnippets($discGroups);
    }

    /**
     * Load the legacy snippet by default; switch to PsvDiscPagePinned when the
     * pinned-layout toggle is on. Default (toggle off) renders byte-identical
     * to the pre-PR behaviour, so deploys are no-op until an operator opts in.
     */
    #[\Override]
    public function getSnippet($className = null)
    {
        if ($className === null && $this->isPinnedLayout()) {
            $className = 'PsvDiscPagePinned';
        }
        return parent::getSnippet($className);
    }

    /**
     * SystemParameter PSV_DISC_PINNED_LAYOUT acts as a feature toggle. Treat
     * any value that's not an explicit "1" as off, so the row can ship with
     * a default of '0' and only switches on once an operator changes it.
     */
    private function isPinnedLayout(): bool
    {
        $repo = $this->getRepoManager()?->get('SystemParameter');
        return $repo?->fetchValue(SystemParameter::PSV_DISC_PINNED_LAYOUT) === '1';
    }

    /**
     * Resolve the four stationery-alignment bookmarks (row heights + line
     * spacing) from SystemParameter, falling back to DEFAULT_ALIGNMENT when
     * the value is missing or non-numeric. Only consulted when the pinned
     * layout toggle is on.
     */
    private function resolveAlignment(): array
    {
        $repo = $this->getRepoManager()?->get('SystemParameter');
        $resolved = [];
        foreach (self::DEFAULT_ALIGNMENT as $key => $default) {
            $value = $repo?->fetchValue($key);
            $resolved[$key] = is_numeric($value) ? (string)(int)$value : (string)$default;
        }
        return $resolved;
    }

    #[\Override]
    protected function getQueryClass(): string
    {
        return Qry::class;
    }
}
