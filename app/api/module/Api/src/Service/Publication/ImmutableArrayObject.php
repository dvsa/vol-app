<?php

namespace Dvsa\Olcs\Api\Service\Publication;

/**
 * @template-extends \ArrayObject<int, mixed>
 */
class ImmutableArrayObject extends \ArrayObject
{
    public function offsetSet($index, $newval): void
    {
    }

    public function offsetUnset($index): void
    {
    }

    public function exchangeArray($input): array
    {
        return [];
    }
}
