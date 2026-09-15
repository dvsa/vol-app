<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Abstract Disc list bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractDiscList extends DynamicBookmark
{
    /**
     * We have to split some fields if they exceed this length
     */
    public const MAX_LINE_LENGTH = 23;

    /**
     * No disc content? No problem
     */
    public const PLACEHOLDER = 'XXXXXXXXXX';

    /**
     * Discs per page - any shortfall will be voided with placeholders
     */
    public const PER_PAGE = 6;

    public const PER_ROW = 1;

    public const BOOKMARK_PREFIX = '';

    /**
     * Let the parser know we've already formatted our content by the
     * time it has been rendered
     */
    public const PREFORMATTED = true;

    /**
     * A minimal paragraph (1 twip line, 0.5pt font) carrying a page-break-
     * before. Emitted between page groups when the pinned layout is on so
     * every sheet starts at the top margin: LibreOffice's continuous-flow
     * table pagination pushes rows a sheet early and the error then
     * compounds across the whole batch (VOL disc printing on Gotenberg).
     */
    protected const PAGE_BREAK_RTF = '\pard\plain\sl-1\slmult0\fs1\pagebb\par ';

    protected $discBundle = [];

    protected $service;

    /**
     * @psalm-return class-string<QueryInterface>
     */
    abstract protected function getQueryClass(): string;

    #[\Override]
    public function getQuery(array $data)
    {
        $queryClass = $this->getQueryClass();

        $queries = [];
        foreach ($data as $key => $id) {
            if (is_int($key)) {
                $queries[] = (new $queryClass())::create(['id' => $id, 'bundle' => $this->discBundle]);
            }
        }

        return $queries;
    }

    /**
     * Split a string into N array parts based on a predefined
     * constant max line length
     */
    protected function splitString($str)
    {
        return str_split((string) $str, static::MAX_LINE_LENGTH);
    }

    /**
     * Return either PREFIX1_ or PREFIX2_ based on a given index
     */
    protected function getPrefix($index)
    {
        $prefix = ($index % static::PER_ROW) + 1;
        return static::BOOKMARK_PREFIX . $prefix . '_';
    }

    protected function renderSnippets($snippets)
    {
        $snippet = $this->getSnippet();
        $parser  = $this->getParser();

        $perPage = $this->snippetsPerPage();

        // at last, we can loop through each group and run a sub
        // replacement on its tokens
        $str = '';
        foreach (array_values($snippets) as $i => $tokens) {
            if ($perPage > 0 && $i > 0 && $i % $perPage === 0) {
                $str .= static::PAGE_BREAK_RTF;
            }
            $str .= $parser->replace($snippet, $tokens);
        }
        return $str;
    }

    /**
     * How many snippets make up one printed page, for hard page-break
     * insertion between page groups. 0 (default) preserves the legacy
     * continuous-flow output byte-for-byte.
     */
    protected function snippetsPerPage(): int
    {
        return 0;
    }

    /**
     * Format date
     *
     * @param string $backendDate date
     *
     * @return string
     */
    protected function formatDate($backendDate)
    {
        return (new \DateTime($backendDate))->format('d-m-Y');
    }
}
