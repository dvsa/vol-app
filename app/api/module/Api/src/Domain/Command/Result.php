<?php

namespace Dvsa\Olcs\Api\Domain\Command;

/**
 * Command Result
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class Result
{
    /**
     * @var array
     */
    private $ids = [];

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var array
     */
    private $flags = [];

    /**
     * Add id to result
     *
     * @param string  $name     Name of related object
     * @param int     $id       Id Value
     * @param boolean $multiple whether to allow multiple IDs of same type,
     *                          the default behaviour is that subsequent IDs just override the first one
     *
     * @return $this
     */
    public function addId($name, $id, $multiple = false)
    {
        if ($multiple) {
            if (isset($this->ids[$name])) {
                if (!is_array($this->ids[$name])) {
                    $current = $this->ids[$name];
                    $this->ids[$name] = [$current];
                }
                array_push($this->ids[$name], $id);
            } else {
                $this->ids[$name] = $id;
            }

            return $this;
        }

        $this->ids[$name] = $id;

        return $this;
    }

    /**
     * Get Id by name of related object
     *
     * @param string $name Name of related object
     *
     * @return mixed|null
     */
    public function getId($name)
    {
        return $this->ids[$name] ?? null;
    }

    /**
     * Return ids
     *
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * Add new message text to result
     *
     * @param string $message message text
     *
     * @return $this
     */
    public function addMessage($message)
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * Return messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Set flag
     *
     * @param string $name  Name of flag
     * @param mixed  $value flag value
     *
     * @return $this
     */
    public function setFlag($name, mixed $value)
    {
        $this->flags[$name] = $value;
        return $this;
    }

    /**
     * Get flag value
     *
     * @param string $name Name of flag
     *
     * @return mixed
     */
    public function getFlag($name)
    {
        return $this->flags[$name] ?? null;
    }

    /**
     * Return the array of flags
     *
     * @return array
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Merge passed result in this Result
     *
     * @param Result $result    Object necessary to merge in
     * @param bool   $recursive Whether to merge recursively
     *
     * @return void
     */
    public function merge(Result $result, bool $recursive = false)
    {
        $this->ids = $recursive
            ? array_merge_recursive($this->ids, $result->getIds())
            : array_merge($this->ids, $result->getIds());
        $this->messages = array_merge($this->messages, $result->getMessages());
        $resultFlags = $result->getFlags();
        // not ideal but it saves a time to fix 800+ unit tests
        if (!empty($resultFlags)) {
            $this->flags = array_merge($this->flags, $resultFlags);
        }
    }

    /**
     * Returns the object represented as an array, minus the flags
     *
     * @return array
     */
    public function toArray()
    {
        $retv = [
            'id' => $this->ids,
            'messages' => $this->messages,
        ];
        // not ideal but it saves a time to fix 800+ unit tests
        if (!empty($this->flags)) {
            $retv['flags'] = $this->flags;
        }
        return $retv;
    }
}
