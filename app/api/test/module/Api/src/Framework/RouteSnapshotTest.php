<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework;

use Dvsa\OlcsTest\Api\Framework\Support\ApiApplication;
use Dvsa\OlcsTest\Api\Framework\Support\Entrypoint;
use Dvsa\OlcsTest\Api\Framework\Support\FrameworkTestCase;
use Dvsa\OlcsTest\Api\Framework\Support\JsonSnapshot;
use Laminas\Http\Request;
use Laminas\Router\RouteStackInterface;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Which route, controller and DTO every api URL reaches, as committed JSON (snapshots/routes/).
 *
 * The api's ~1,100 routes are a nested laminas-router tree: Literal and Segment routes down to a Method (or
 * olcs-transfer Query) route per HTTP verb, whose `dto` default names the command or query. The config
 * lives in lib/olcs-transfer and serves both sides: the frontends assemble API URLs from it (the
 * `ApiRouter` service, `api_router` config) and the api matches them (`router` config). Whatever replaces
 * the laminas-mvc routing must send every one of these requests to the same place, including the cases
 * where today's tree is surprising.
 *
 * For every terminating route the snapshot records:
 *  - request: the method and URL a frontend sends, assembled the way Common\Service\Cqrs builds it (with
 *    the sample segment values below; Query routes put every parameter, including `dto`, in the query string)
 *  - matches/params: what the api's router does with that request — normally the same route, but not
 *    always (e.g. api/presiding-tc/user-list/GET is shadowed by the unconstrained api/presiding-tc/:id)
 *  - withoutTrailingSlash (or withTrailingSlash): what the same path matches without the trailing slash
 *    the frontends send (or with one), as other clients send either
 *
 * Segment values are 101, 102, … by position, so a parameter landing under the wrong name shows; where a
 * route constrains a parameter, the first sample value that satisfies the constraint is used.
 *
 * Regenerate only for an intended route change, and review the diff.
 */
final class RouteSnapshotTest extends FrameworkTestCase
{
    private const string SNAPSHOT_DIR = __DIR__ . '/snapshots/routes';
    private const string UPDATE_COMMAND = 'UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter RouteSnapshotTest';
    private const string NO_SNAPSHOTS = '(no snapshots)';

    /**
     * For constrained parameters, tried in order after the positional number
     */
    private const array CONSTRAINED_SAMPLES = ['abc', 'OLCS-01-AB'];

    /**
     * The routes are computed once per process: they are plain data, and building them costs ~0.3s
     *
     * @var array<string, array<string, array<string, mixed>>>|null group => route name => entry
     */
    private static ?array $routes = null;

    #[DataProvider('snapshotGroups')]
    public function testRoutesMatchTheirSnapshot(string $group): void
    {
        $this->assertNotSame(
            self::NO_SNAPSHOTS,
            $group,
            sprintf('There are no route snapshots. Create them with `%s` and review the new files.', self::UPDATE_COMMAND)
        );

        $routes = self::routes();

        $this->assertArrayHasKey(
            $group,
            $routes,
            sprintf('No routes under %s any more. Run `%s` to remove the snapshot.', $group, self::UPDATE_COMMAND)
        );

        JsonSnapshot::assertMatches(self::snapshotFile($group), $routes[$group], 'Routes under ' . $group, self::UPDATE_COMMAND);
    }

    public function testEveryRouteGroupHasASnapshot(): void
    {
        $routes = self::routes();
        $this->assertGreaterThan(1000, array_sum(array_map(count(...), $routes)), 'The route tree was not found');

        $missing = [];

        foreach ($routes as $group => $entries) {
            if (JsonSnapshot::isUpdating()) {
                JsonSnapshot::assertMatches(self::snapshotFile($group), $entries, 'Routes under ' . $group, self::UPDATE_COMMAND);
            } elseif (!is_file(self::snapshotFile($group))) {
                $missing[] = $group;
            }
        }

        $this->assertSame(
            [],
            $missing,
            sprintf('These routes have no snapshot. Create them with `%s` and review the new files.', self::UPDATE_COMMAND)
        );

        JsonSnapshot::assertNoOrphans(
            self::SNAPSHOT_DIR,
            array_map(self::snapshotFile(...), array_keys($routes)),
            self::UPDATE_COMMAND
        );
    }

    /**
     * The groups that have a snapshot; testEveryRouteGroupHasASnapshot catches groups that do not
     *
     * @return iterable<string, array{string}>
     */
    public static function snapshotGroups(): iterable
    {
        $files = is_dir(self::SNAPSHOT_DIR)
            ? new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(self::SNAPSHOT_DIR, \FilesystemIterator::SKIP_DOTS)
            )
            : [];

        $groups = [];

        foreach ($files as $file) {
            $groups[] = substr($file->getPathname(), strlen(self::SNAPSHOT_DIR) + 1, -strlen('.json'));
        }

        sort($groups);

        // PHPUnit rejects an empty data set; this one fails with instructions instead
        if ($groups === []) {
            $groups[] = self::NO_SNAPSHOTS;
        }

        foreach ($groups as $group) {
            yield $group => [$group];
        }
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    private static function routes(): array
    {
        if (self::$routes !== null) {
            return self::$routes;
        }

        $serviceManager = ApiApplication::loadModules(Entrypoint::Web);

        $config = $serviceManager->get('config');
        $apiRouter = $serviceManager->get('ApiRouter');
        $router = $serviceManager->get('HttpRouter');
        assert($apiRouter instanceof RouteStackInterface && $router instanceof RouteStackInterface);

        // The frontends assemble from the api_router copy of the tree. The api's own non-transfer routes
        // (search, scanning) are not in it; those are assembled from the api router itself.
        $frontendRoutes = $config['api_router']['routes']['api']['child_routes']['backend']['child_routes'];

        $routes = [];

        foreach (self::terminatingRoutes($config['router']['routes']) as $name => $route) {
            $assembler = isset($frontendRoutes[explode('/', $name)[0]])
                ? static fn(array $values): string => $apiRouter->assemble($values, ['name' => 'api/backend/' . $name])
                : static fn(array $values): string => $router->assemble($values, ['name' => $name]);

            $routes[self::group($name)][$name] = self::describe($route, $assembler, $router);
        }

        ksort($routes);

        return self::$routes = $routes;
    }

    /**
     * Walks the route tree, yielding every route that can terminate a match, with the segment parameters
     * and constraints it inherits from its parents.
     *
     * @param array<string, array<string, mixed>> $routes
     * @param list<string> $parameters
     * @param array<string, string> $constraints
     *
     * @return iterable<string, array{verb: ?string, parameters: list<string>, constraints: array<string, string>}>
     */
    private static function terminatingRoutes(
        array $routes,
        string $prefix = '',
        array $parameters = [],
        array $constraints = []
    ): iterable {
        foreach ($routes as $name => $route) {
            $fullName = $prefix === '' ? $name : $prefix . '/' . $name;
            $options = $route['options'] ?? [];

            $routeParameters = $parameters;

            if (isset($options['route']) && preg_match_all('/:([a-zA-Z_][a-zA-Z0-9_]*)/', $options['route'], $matches)) {
                array_push($routeParameters, ...$matches[1]);
            }

            $routeConstraints = array_merge($constraints, $options['constraints'] ?? []);
            $children = $route['child_routes'] ?? [];

            // laminas-router: a route with children terminates only if it says so; one without always does
            if ($children === [] || ($route['may_terminate'] ?? false)) {
                yield $fullName => [
                    'verb' => $options['verb'] ?? null,
                    'parameters' => $routeParameters,
                    'constraints' => $routeConstraints,
                ];
            }

            if ($children !== []) {
                yield from self::terminatingRoutes($children, $fullName, $routeParameters, $routeConstraints);
            }
        }
    }

    /**
     * @param array{verb: ?string, parameters: list<string>, constraints: array<string, string>} $route
     * @param \Closure(array<string, string>): string $assemble
     *
     * @return array<string, mixed>
     */
    private static function describe(array $route, \Closure $assemble, RouteStackInterface $router): array
    {
        $values = [];

        foreach ($route['parameters'] as $position => $parameter) {
            $value = self::sampleValue($position, $route['constraints'][$parameter] ?? null);

            if ($value === null) {
                return ['unassemblable' => sprintf('no sample value satisfies %s: %s', $parameter, $route['constraints'][$parameter])];
            }

            $values[$parameter] = $value;
        }

        $uri = $assemble($values);

        $path = (string) parse_url($uri, PHP_URL_PATH);
        parse_str((string) parse_url($uri, PHP_URL_QUERY), $query);
        $method = $route['verb'] ?? 'GET';

        $match = self::match($router, $method, $path);

        $entry = [
            'request' => ($route['verb'] ?? 'any') . ' ' . $path,
        ];

        if ($query !== []) {
            ksort($query);
            $entry['query'] = $query;
        }

        $entry['matches'] = $match['route'];

        if ($match['params'] !== null) {
            $entry['params'] = $match['params'];
        }

        if (str_ends_with($path, '/')) {
            $entry['withoutTrailingSlash'] = self::match($router, $method, rtrim($path, '/'))['route'];
        } else {
            $entry['withTrailingSlash'] = self::match($router, $method, $path . '/')['route'];
        }

        return $entry;
    }

    private static function sampleValue(int $position, ?string $constraint): ?string
    {
        foreach ([(string) (101 + $position), ...self::CONSTRAINED_SAMPLES] as $candidate) {
            if ($constraint === null || preg_match('(\A(?:' . $constraint . ')\z)', $candidate) === 1) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array{route: ?string, params: ?array<string, mixed>}
     */
    private static function match(RouteStackInterface $router, string $method, string $path): array
    {
        $request = new Request();
        $request->setMethod($method);
        $request->setUri('http://olcs-backend' . $path);

        $match = $router->match($request);

        if ($match === null) {
            return ['route' => null, 'params' => null];
        }

        $params = $match->getParams();
        ksort($params);

        return ['route' => $match->getMatchedRouteName(), 'params' => $params];
    }

    /**
     * api/licence/single/GET is in group api/licence; search and document are groups of their own
     */
    private static function group(string $name): string
    {
        return implode('/', array_slice(explode('/', $name), 0, 2));
    }

    private static function snapshotFile(string $group): string
    {
        return self::SNAPSHOT_DIR . '/' . $group . '.json';
    }
}
