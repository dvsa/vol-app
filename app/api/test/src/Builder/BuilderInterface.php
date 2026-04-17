<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Builder;

interface BuilderInterface
{
    /**
     * Builds an object.
     *
     * @return mixed
     */
    public function build(): mixed;
}
