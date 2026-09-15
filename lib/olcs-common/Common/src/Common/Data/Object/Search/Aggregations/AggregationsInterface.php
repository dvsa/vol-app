<?php

namespace Common\Data\Object\Search\Aggregations;

interface AggregationsInterface
{
    /**
     * Contains the key of the aggregation / filter, set in class definition.
     *
     * @return string
     */
    public function getKey();

    /**
     * Contains the title of the aggregation / filter, set in class definition.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Contains the current value of the aggregation / filter. Set using @see setValue().
     *
     * @return string
     */
    public function getValue();

    /**
     * Set the aggregation value.
     *
     * @return $this
     */
    public function setValue(mixed $value);
}
