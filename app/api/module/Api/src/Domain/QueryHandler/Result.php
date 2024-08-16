<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler;

/**
 * Result
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Result
{
    /**
     * @var BundleSerializableInterface
     */
    protected $object;

    /**
     * @var array
     */
    protected $bundle;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * Constructor
     *
     * @param BundleSerializableInterface $object object
     * @param array                       $bundle bundle
     * @param array                       $values values
     *
     * @return void
     */
    public function __construct(BundleSerializableInterface $object = null, array $bundle = [], array $values = [])
    {
        $this->object = $object;
        $this->bundle = $bundle;
        $this->values = $values;
    }

    /**
     * Set value
     *
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return void
     */
    public function setValue($key, mixed $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * Recursively serialize objects based on the bundle
     *
     * @return array
     */
    public function serialize()
    {
        if ($this->isEmpty()) {
            return null;
        }

        $data = $this->object->serialize($this->bundle);

        return array_replace_recursive($data, $this->values);
    }

    /**
     * Check if Result is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->object === null;
    }

    public function getObject()
    {
        return $this->object;
    }
}
