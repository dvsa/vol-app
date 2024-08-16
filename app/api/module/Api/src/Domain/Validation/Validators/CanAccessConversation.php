<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Repository\MessagingConversation;

class CanAccessConversation extends AbstractCanAccessEntity
{
    protected $repo = MessagingConversation::class;
}
