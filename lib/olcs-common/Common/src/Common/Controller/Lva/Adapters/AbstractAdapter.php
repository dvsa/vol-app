<?php

namespace Common\Controller\Lva\Adapters;

use Common\Controller\Lva\Interfaces\AdapterInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class AbstractAdapter implements AdapterInterface
{
    protected $lva;

    protected $applicationAdapter;

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get an instance of application lva adapter
     */
    protected function getApplicationAdapter(): AbstractLvaAdapter
    {
        if ($this->applicationAdapter === null) {
            $this->applicationAdapter = $this->getLvaAdapter('Application');
        }

        return $this->applicationAdapter;
    }

    /**
     * Get an instance of an Lva Adapter
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getLvaAdapter(?string $lva = null): AbstractLvaAdapter
    {
        if ($lva === null) {
            $lva = $this->lva;
        }

        return $this->container->get($lva . 'LvaAdapter');
    }
}
