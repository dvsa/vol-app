<?php

namespace Dvsa\Olcs\Transfer\Util;

class ChildRoutesGenerator
{
    /**
     * ChildRoutesGenerator constructor.
     */
    public function __construct(private array $routes, private string $directory)
    {
    }

    private function buildChildRoutes($directory, array $child = []): array
    {
        $paths = scandir($directory);

        foreach ($paths as $path) {
            if (str_starts_with($path, '.')) {
                continue;
            }

            if (!is_dir($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $childRoot = include $directory . '/' . $path;
                $child[key($childRoot)] = current($childRoot);
            } else {
                $child[$path] = [
                    'child_routes' => []
                ];
                $child[$path]['child_routes'] = $this->buildChildRoutes($directory . '/' . $path, $child[$path]['child_routes']);
            }
        }

        return $child;
    }

    public function getUpdatedRoutes(): array
    {
        $childRoutes = $this->buildChildRoutes($this->directory);

        foreach ($childRoutes as $routeName => $config) {
            if (array_key_exists($routeName, $this->routes['api']['child_routes'])) {
                $this->routes['api']['child_routes'][$routeName] = array_merge_recursive(
                    $this->routes['api']['child_routes'][$routeName],
                    $childRoutes[$routeName]
                );
            }
        }

        return $this->routes;
    }
}
