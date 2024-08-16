<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PsvDiscBundle as Qry;

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

    protected $discBundle = [
        'licence' => [
            'organisation'
        ]
    ];

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

        // bit ugly, but now we have to chunk the discs into N per page
        $discGroups = [];
        for ($i = 0; $i < count($discs); $i += self::PER_PAGE) {
            $pageDiscs = [];
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

    protected function getQueryClass(): string
    {
        return Qry::class;
    }
}
