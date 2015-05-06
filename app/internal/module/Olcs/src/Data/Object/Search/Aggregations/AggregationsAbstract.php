<?php
namespace Olcs\Data\Object\Search\Aggregations;

abstract class AggregationsAbstract extends ArrayObject
    implements AggregationsInterface
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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
}
