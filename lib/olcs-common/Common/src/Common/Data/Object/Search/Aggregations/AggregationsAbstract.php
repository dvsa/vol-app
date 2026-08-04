<?php

namespace Common\Data\Object\Search\Aggregations;

abstract class AggregationsAbstract implements AggregationsInterface
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title;

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key;

    /**
     * The value to return to the search to filter upon.
     *
     * @var string
     */
    protected $value = '';

    /**
     * @return mixed
     */
    #[\Override]
    public function getKey()
    {
        return $this->key;
    }

    public function setKey(mixed $key): void
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    #[\Override]
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    #[\Override]
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    #[\Override]
    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle(mixed $title): void
    {
        $this->title = $title;
    }
}
