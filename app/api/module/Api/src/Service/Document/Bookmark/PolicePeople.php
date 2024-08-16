<?php

/** Police People
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PolicePeopleBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\Name as NameFormatter;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\Date as DateFormatter;

/**
 * Police People
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PolicePeople extends DynamicBookmark
{
    public const BOLD_START = '{\b ';
    public const BOLD_END = '\b0}';
    public const PREFORMATTED = true;
    public const HEADING_LINE = '{\b People associated with Licences in this document \b0\par\par }';
    public const NO_ENTRIES = 'No entries';

    /**
     * Query to retrieve data
     *
     * @param array $data
     * @return array
     */
    public function getQuery(array $data)
    {
        $bundle = [
            'publicationLinks' => [
                'licence',
                'publicationSection',
                'policeDatas',
                'publication'
            ]
        ];

        return Qry::create(['id' => $data['id'], 'bundle' => $bundle]);
    }

    /**
     * Renders the bookmark
     *
     * @return string
     */
    public function render()
    {
        $str = self::HEADING_LINE;

        if (empty($this->data)) {
            return $str . self::NO_ENTRIES;
        }

        $rows[0] = [
            'BOOKMARK1' => self::BOLD_START . ' Name ' . self::BOLD_END,
            'BOOKMARK2' => self::BOLD_START . ' D.O.B. ' . self::BOLD_END,
            'BOOKMARK3' => self::BOLD_START . ' Licence no. ' . self::BOLD_END,
            'BOOKMARK4' => self::BOLD_START . ' Section ' . self::BOLD_END,
        ];

        foreach ($this->data['publicationLinks'] as $pubLink) {
            foreach ($pubLink['policeDatas'] as $police) {
                $birthDate = ($police['birthDate'] ? DateFormatter::format([$police['birthDate']]) : 'Unknown');

                //depending on the publication type, work out the section the person appeared in
                $sectionKey = ($pubLink['publication']['pubType'] === 'A&D' ? 'adSection' : 'npSection');

                $rows[] = [
                    'BOOKMARK1' => NameFormatter::format($police),
                    'BOOKMARK2' => $birthDate,
                    'BOOKMARK3' => $pubLink['licence']['licNo'] ?? null,
                    'BOOKMARK4' => $pubLink['publicationSection'][$sectionKey]
                ];
            }
        }

        $snippet = $this->getSnippet('CHECKLIST_4CELL_TABLE');
        $parser  = $this->getParser();

        foreach ($rows as $tokens) {
            $str .= $parser->replace($snippet, $tokens);
        }

        return $str;
    }
}
