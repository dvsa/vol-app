<?php

/**
 * Crud Aggregate Interface
 *
 * @package Common\Crud
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Common\Crud;

/**
 * Crud Aggregate Interface
 *
 * @package Common\Crud
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
interface AggregateInterface extends
    CreateInterface,
    RetrieveInterface,
    UpdateInterface,
    DeleteInterface
{
    //
}
