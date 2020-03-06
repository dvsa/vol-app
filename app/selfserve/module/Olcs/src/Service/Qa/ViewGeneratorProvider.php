<?php

namespace Olcs\Service\Qa;

use Olcs\Service\Qa\ViewGenerator\ViewGeneratorInterface;
use RuntimeException;

class ViewGeneratorProvider
{
    /** @var array */
    private $viewGenerators;

    /**
     * Get a Form instance corresponding to the supplied form data
     *
     * @param string $routeName
     *
     * @return ViewGeneratorInterface
     *
     * @throws RuntimeException
     */
    public function getByRouteName($routeName)
    {
        if (!isset($this->viewGenerators[$routeName])) {
            throw new RuntimeException('No view generator found for route ' . $routeName);
        }

        return $this->viewGenerators[$routeName];
    }

    /**
     * Register an instance of ViewGeneratorInterface against the specified route name
     *
     * @param string $routeName
     * @param ViewGeneratorInterface $viewGenerator
     */
    public function registerViewGenerator($routeName, ViewGeneratorInterface $viewGenerator)
    {
        $this->viewGenerators[$routeName] = $viewGenerator;
    }
}
