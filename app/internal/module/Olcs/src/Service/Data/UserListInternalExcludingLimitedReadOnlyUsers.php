<?php

namespace Olcs\Service\Data;

class UserListInternalExcludingLimitedReadOnlyUsers extends UserListInternal
{
    /**
     * @var bool
     */
    protected $excludeLimitedReadOnly = true;
}
