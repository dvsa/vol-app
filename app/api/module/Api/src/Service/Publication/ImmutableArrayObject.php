<?php

namespace Dvsa\Olcs\Api\Service\Publication;

/**
 * @template-extends \ArrayObject<int, mixed>
 */
class ImmutableArrayObject extends \ArrayObject
{
    #[\Override]
    public function offsetSet($index, $newval): void
    {
    }

    #[\Override]
    public function offsetUnset($index): void
    {
    }

    #[\Override]
    public function exchangeArray($input): array
    {
        return [];
    }
}
