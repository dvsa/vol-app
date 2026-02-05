<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Stub;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;

class AbstractContextStub extends AbstractContext
{
    /** @SuppressWarnings("unused") */
    public function provide(PublicationLink $publication, \ArrayObject $context): array
    {
    }
}
