<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Listener\Stub;

use Doctrine\Persistence\NotifyPropertyChanged;
use Doctrine\Persistence\PropertyChangedListener;

/**
 * Stub for emulation Entity object with LastModified fields in test @see OlcsEntityListenerTest
 */
class EntityStub implements NotifyPropertyChanged
{
    protected $lastModifiedBy;

    public function setLastModifiedBy(mixed $lastModifiedBy): void
    {
        $this->lastModifiedBy = $lastModifiedBy;
    }

    public function addPropertyChangedListener(PropertyChangedListener $listener): void
    {
    }
}
