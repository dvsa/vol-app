<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Adapter\Stub;

use Laminas\Authentication\Adapter\AbstractAdapter;
use Laminas\Authentication\Result;

/**
 * A concrete adapter for the one factory branch that constructs the configured class itself.
 *
 * That branch does `new $adapterClass($adapterConfig)`, so it needs a real class taking the
 * adapter config array — an interface name cannot stand in for it.
 *
 * @see \Dvsa\Olcs\Auth\Adapter\ValidatableAdapterFactory
 */
class ValidatableAdapterStub extends AbstractAdapter
{
    public function __construct(public readonly array $config = [])
    {
    }

    #[\Override]
    public function authenticate(): Result
    {
        return new Result(Result::SUCCESS, $this->identity);
    }
}
