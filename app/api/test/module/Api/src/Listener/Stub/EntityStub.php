<?php

namespace Dvsa\OlcsTest\Api\Listener\Stub;

use Doctrine\Persistence\NotifyPropertyChanged;
use Doctrine\Persistence\PropertyChangedListener;

/**
 * Stub for emulation Entity object with LastModified fields in test @see OlcsEntityListenerTest
 */
class EntityStub implements NotifyPropertyChanged
{
    protected $lastModifiedBy;

    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;
    }

    public function addPropertyChangedListener(PropertyChangedListener $listener)
    {
    }
}
