<?php

namespace Dvsa\Olcs\Db\Exceptions;

use Throwable;

/**
 * Class SearchDateFilterParseException
 *
 * @package Olcs\Db\Exceptions
 */
class SearchDateFilterParseException extends \Exception
{
    private $dateField;

    /**
     * Get the date field associated with exception
     *
     * @return mixed field associated with exception
     */
    public function getDateField()
    {
        return $this->dateField;
    }

    /**
     * Set the date field associated with exception
     *
     * @param mixed $dateField field associated with exception
     *
     * @return void
     */
    public function setDateField(mixed $dateField)
    {
        $this->dateField = $dateField;
    }


    /**
     * SearchDateFilterParseException constructor.
     *
     * @param string         $message  message from exception
     * @param int            $code     error code
     * @param Throwable|null $previous previous exception
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
