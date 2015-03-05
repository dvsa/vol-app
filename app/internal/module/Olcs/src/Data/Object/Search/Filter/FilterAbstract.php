<?php
namespace Olcs\Data\Object\Search\Filter;
use Zend\Stdlib\ArrayObject;

/**
 * Abstract class for the search filter classes.
 *
 * @package Olcs\Data\Object\Search\Filter
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
abstract class FilterAbstract extends ArrayObject
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
     * Contains the filterable options values. Populated by the search client.
     *
     * @var array
     */
    protected $options = [];

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

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }
}
