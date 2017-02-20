<?php

namespace Olcs\Service\Data;

/**
 * User Internal Team List data service. Returns a list of internal users and their respective team info.
 *
 * @package Olcs\Service\Data
 */
class UserInternalTeamList extends UserListInternal
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
