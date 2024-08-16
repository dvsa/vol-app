<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\DocParagraphBundle as Qry;

/**
 * Text block bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class TextBlock extends DynamicBookmark
{
    /**
     * Get the query used to get data required to populate the bookmark
     *
     * @param array $data Data
     *
     * @return null|Qry
     */
    public function getQuery(array $data)
    {
        // TextBlocks are used as fallbacks when there isn't a more
        // specific bookmark for a given token. As such there's a good
        // chance we don't actually have any data in our `bookmarks`
        // array to satisfy this text block at all, so we need to
        // be defensive
        if (!isset($data['bookmarks'][$this->token])) {
            return null;
        }
        $paragraphs = $data['bookmarks'][$this->token];
        $queries = [];
        foreach ($paragraphs as $paragraphId) {
            $query = Qry::create(['id' => $paragraphId]);
            $queries[] = $query;
        }

        return $queries;
    }

    /**
     * Render the bookmark
     *
     * @return string
     */
    public function render()
    {
        /**
         * At render time, we might have an array or a string. If we've got
         * a string then just dump that out verbatim
         */
        if (!is_array($this->data)) {
            return $this->data;
        }

        /**
         * Otherwise, if we've got an array we assume it has a known 'paraText' key
         * because it was populated by a backend entity
         */
        $result = "";
        foreach ($this->data as $paragraph) {
            // Data from DB is unicode, but the templates arent
            $result .= mb_convert_encoding($paragraph['paraText'], 'ISO-8859-1') . "\n";
        }
        return substr($result, 0, -1);
    }
}
