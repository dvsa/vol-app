<?php

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NotIsAnonymousUser;

return [
   \Dvsa\Olcs\Api\Domain\QueryHandler\DvlaSearch\Vehicle::class => NotIsAnonymousUser::class
];
