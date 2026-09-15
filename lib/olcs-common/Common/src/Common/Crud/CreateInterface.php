<?php

/**
 * Crud Create Interface
 *
 * @package Common\Crud
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Common\Crud;

/**
 * Crud Create Interface
 *
 * @package Common\Crud
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
interface CreateInterface
{
    /**
     * Creates a single record.
     *
     * @param array $data data to create
     *
     * @return bool
     */
    public function create(array $data);
}
