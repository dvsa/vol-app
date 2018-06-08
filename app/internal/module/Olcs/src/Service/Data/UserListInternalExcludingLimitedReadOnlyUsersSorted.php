<?php

namespace Olcs\Service\Data;

/**
 * Class UserListInternalExcludingLimitedReadOnlyUsersSorted
 *
 * @package Olcs\Service\Data
 */
class UserListInternalExcludingLimitedReadOnlyUsersSorted extends UserListInternalExcludingLimitedReadOnlyUsers
{
    /**
     * @var string
     */
    protected static $sort = 't.name, p.forename';

    /**
     * @var string
     */
    protected static $order = 'ASC';
}
