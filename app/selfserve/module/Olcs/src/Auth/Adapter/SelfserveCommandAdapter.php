<?php

declare(strict_types=1);

namespace Olcs\Auth\Adapter;

use Common\Auth\Adapter\CommandAdapter;

class SelfserveCommandAdapter extends CommandAdapter
{
    protected $realm = 'selfserve';
}
