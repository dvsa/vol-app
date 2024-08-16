<?php

/**
 * SingleValueAbstract
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\FormatterInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * SingleValueAbstract
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
abstract class SingleValueAbstract extends DynamicBookmark
{
    public const CLASS_NAMESPACE = __NAMESPACE__; // do not change/override this.
    public const FORMATTER = null;                // The formater (or null) used when rendering the bookmark
    public const FIELD = null;                    // The array key from retrieved data to render, eg "hearingDate"
    public const SRCH_FLD_KEY = 'id';             // The parameter name of the Query DTO
    public const SRCH_VAL_KEY = 'busRegId';       // The key to search for in query data, that will be assigned to SRCH_FLD_KEY
    public const DEFAULT_VALUE = null;            // Default value to render
    public const QUERY_CLASS = null;              // Query DTO class name
    public const BUNDLE = [];                     // The bundle, passed to the Query DTO

    /**
     * get query
     *
     * @param array $data query data
     *
     * @return QueryInterface
     */
    public function getQuery(array $data)
    {
        $data = [
            static::SRCH_FLD_KEY => $data[static::SRCH_VAL_KEY] ?? null,
            'bundle' => static::BUNDLE
        ];

        $queryClass = static::QUERY_CLASS;

        return $queryClass::create($data);
    }

    /**
     * get value
     *
     * @return null|string
     */
    protected function getValue()
    {
        return $this->data[static::FIELD] ?? null;
    }

    /**
     * Render the bookmark
     *
     * @return null|string
     */
    public function render()
    {
        $value = $this->getValue();

        $formatter = static::FORMATTER;

        if (!is_null($value) && !is_null($formatter)) {

            /**
             * @var FormatterInterface $class
             */
            $class = __NAMESPACE__ . '\Formatter\\' . $formatter;

            $value = $class::format((array)$value);
        }

        if (empty($value) && static::DEFAULT_VALUE !== null) {
            $value = static::DEFAULT_VALUE;
        }

        return $value;
    }
}
