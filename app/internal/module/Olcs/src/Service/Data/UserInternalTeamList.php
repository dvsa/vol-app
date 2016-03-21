<?php

/**
 * User Internal Team List data service
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
namespace Olcs\Service\Data;

/**
 * User Internal Team List data service. Returns a list of internal users and their respective team info.
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class UserInternalTeamList extends UserListInternal
{
    protected $sort = 't.name, p.forename';

    protected $order = 'ASC';
}
