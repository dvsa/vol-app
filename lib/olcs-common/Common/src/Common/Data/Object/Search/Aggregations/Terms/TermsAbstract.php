<?php

namespace Common\Data\Object\Search\Aggregations\Terms;

use Common\Data\Object\Search\Aggregations\AggregationsAbstract;

/**
 * Abstract class for the search filter classes.
 *
 * @package Common\Data\Object\Search\Aggregations\Terms
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
abstract class TermsAbstract extends AggregationsAbstract
{
    /**
     * Contains the filterable options values. Populated by the search client.
     *
     * @var array
     */
    protected $options = [];

    public const TYPE_DYNAMIC = 'DYNAMIC';
    public const TYPE_FIXED = 'FIXED';
    public const TYPE_COMPLEX = 'COMPLEX';
    public const TYPE_BOOLEAN = 'BOOLEAN';

    public function getType(): string
    {
        return self::TYPE_DYNAMIC;
    }

    /**
     * @return array
     */
    public function getOptionsKvp()
    {
        $output = [];

        foreach ($this->getOptions() as $option) {
            $output[$option['key']] = $option['key'];
        }

        return $output;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
