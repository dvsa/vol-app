<?php
// @codingStandardsIgnoreFile

/**
 * Release
 *
 * Handle's the release process
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Runner
{
    /**
     * Release types
     */
    public const TYPE_MINOR = 1;

    public const TYPE_MAJOR = 2;

    /**
     * Message types
     */
    public const MESSAGE_DEFAULT = "\e[39m";

    public const MESSAGE_OK = "\e[34m";

    public const MESSAGE_ERROR = "\e[31m";

    public const MESSAGE_SUCCESS = "\e[32m";

    public const MESSAGE_INFO = "\e[33m";

    /**
     * Release type
     *
     * @var int
     */
    private $type = self::TYPE_MINOR;

    /**
     * Holds the nextRelease
     *
     * @var array
     */
    private $nextRelease = [];

    /**
     * Holds the version
     *
     * @var string
     */
    private $version;

    /**
     * Format any command line arguments (In the future)
     */
    public function __construct()
    {
        $options = getopt('t:v:');

        if (isset($options['t']) && defined('self::TYPE_' . strtoupper($options['t']))) {

            $this->setType(constant('self::TYPE_' . strtoupper($options['t'])));
        }

        if (isset($options['v'])) {
            $this->setVersion($options['v']);
        }
    }

    /**
     * Set version number
     *
     * @param string $version
     */
    public function setVersion($version): void
    {
        $this->version = $version;
    }

    /**
     * Setter for type
     *
     * @param int $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * Run the script
     */
    public function run(): void
    {
        try {
            $this->runCommand();
        } catch (Exception $exception) {
            $this->output($exception->getMessage(), self::MESSAGE_ERROR);
        }
    }

    /**
     * Run the command
     *
     * @throws Exception
     */
    private function runCommand(): void
    {
        $this->output('Checking repositories', self::MESSAGE_INFO);

        $version = $this->getVersion();

        $repos = $this->getRepos();

        foreach ($repos as &$repo) {

            $repo->setVersion($version);

            $checkAgain = false;

            $repo->fetchOrigin();

            if (!$repo->isOnDevelop()) {

                $checkAgain = true;
                $repo->checkoutDevelop();
            }

            if ($repo->hasUncommittedChanges()) {
                throw new Exception($repo->getName() . ': Has uncommitted changes');
            }

            if ($checkAgain && !$repo->isOnDevelop()) {
                throw new Exception($repo->getName() . ': Is not on develop, please corrent this before continuing');
            }

            $repo->pullDevelop();
        }

        $this->output('Merging changes into master', self::MESSAGE_INFO);

        foreach ($repos as &$repo) {

            $repo->createRelease();
        }

        $this->updateReleaseVersion();

        foreach ($repos as &$repo) {

            $repo->updateComposerJson();
        }

        foreach ($repos as &$repo) {

            if ($repo->hasUncommittedChanges()) {

                $repo->commitChanges(
                    'Updated composer dependencies and release number: ' . $version
                );
            }

            $repo->publish();
        }
    }

    /**
     * Get version
     *
     * @return string
     */
    private function getVersion()
    {
        if (!$this->version) {
            $version = $this->getNextRelease();

            return $version[0] . '.' . $version[1];
        }

        return $this->version;
    }

    /**
     * Update release version
     */
    private function updateReleaseVersion(): void
    {
        $this->output('Updating release number in config');

        if (file_exists(__DIR__ . '/../Common/config/release.json')) {

            $release = json_decode(file_get_contents(__DIR__ . '/../Common/config/release.json'), true);

            $release['version'] = $this->getVersion();

            if (file_put_contents(__DIR__ . '/../Common/config/release.json', json_encode($release, JSON_UNESCAPED_SLASHES)) === 0 || file_put_contents(__DIR__ . '/../Common/config/release.json', json_encode($release, JSON_UNESCAPED_SLASHES)) === false) {
                throw new Exception('Unable to write to release.json');
            }
        } else {
            // We don't always have a release.json
            //throw new Exception('No release.json found');
        }
    }

    /**
     * Output message
     *
     * @param string $message
     *
     * @psalm-param '[31m'|'[33m'|'[34m' $type
     */
    public function output($message, string $type = self::MESSAGE_OK): void
    {
        echo $type . $message . "\n" . self::MESSAGE_DEFAULT;
    }

    /**
     * Get a list of repos
     *
     * @return array
     */
    private function getRepos()
    {
        return [
            new Repo(__DIR__ . '/../../olcs-common', $this),
            new Repo(__DIR__ . '/../../olcs-backend', $this),
            //new Repo(__DIR__ . '/../../olcs-entities', $this),
            new Repo(__DIR__ . '/../../olcs-internal', $this),
            new Repo(__DIR__ . '/../../olcs-selfserve', $this),
            new Repo(__DIR__ . '/../../olcs-config', $this),
            new Repo(__DIR__ . '/../../olcs-static', $this),
        ];
    }

    /**
     * Get the version number of the next release
     *
     * @return array
     */
    private function getNextRelease()
    {
        if ($this->nextRelease === []) {
            [$lastMajor, $lastMinor] = $this->getLastTag();

            switch($this->type) {
                case self::TYPE_MINOR:
                    $newMajor = $lastMajor;
                    $newMinor = ($lastMinor + 1);
                    break;
                case self::TYPE_MAJOR:
                    $newMajor = ($lastMajor + 1);
                    $newMinor = 0;
                    break;
            }

            $this->nextRelease = [(string)$newMajor, (string)$newMinor];
        }

        return $this->nextRelease;
    }

    /**
     * Get the last version tag
     *
     * @todo implement this when we have a tag
     * @return array
     */
    private function getLastTag()
    {
        $tag = shell_exec('git tag');

        $tag = trim($tag, "\n");

        if ($tag === '' || $tag === '0') {
            throw new Exception('No current tag found');
        }

        $tags = explode("\n", $tag);

        $lastTag = count($tags) === 1 ? $tags[0] : array_pop($tags);

        $lastTag = str_replace('v', '', $lastTag);

        return explode('.', $lastTag, 2);
    }
}

/**
 * Repo object
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Repo
{
    /**
     * The release version number
     *
     * @var string
     */
    private $version;

    /**
     * The repo location
     *
     * @var string
     */
    private $location;

    /**
     * The repo name
     *
     * @var string
     */
    private $name;

    /**
     * The git status
     *
     * @var string
     */
    private $status;

    /**
     * Pass in the location and runner
     *
     * @param string $location
     * @param object $runner
     */
    public function __construct($location, /**
     * The script runner
     */
    private $runner)
    {
        $this->location = realpath($location);

        $parts = explode('/', $this->location);

        $this->name = array_pop($parts);

        $this->loadStatus();
    }

    /**
     * Setter for version
     *
     * @param string $version
     */
    public function setVersion($version): void
    {
        if (empty($version)) {
            throw new Exception($this->getName() . ': Version number is empty');
        }

        $this->version = (string)$version;
    }

    /**
     * Getter for version
     *
     * @return string
     */
    public function getVersion()
    {
        if (empty($this->version)) {

            throw new Exception($this->getName() . ': Version number is empty');
        }

        if (preg_match('/^\d+\.\d+$/', $this->version) === 0 || preg_match('/^\d+\.\d+$/', $this->version) === 0 || preg_match('/^\d+\.\d+$/', $this->version) === false) {
            throw new Exception($this->getName() . ' Invalid version number ' . $this->version);
        }

        return $this->version;
    }

    /**
     * Get the location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Get the name of the repo
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the status of the repo
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the current status
     */
    private function loadStatus(): void
    {
        $this->status = shell_exec('cd ' . $this->getLocation() . ' && git status');
    }

    /**
     * Fetch origin
     */
    public function fetchOrigin(): void
    {
        $this->output('Fetching origin');
        shell_exec('cd ' . $this->getLocation() . ' && git fetch -p origin');
        $this->loadStatus();
    }

    /**
     * Check if a repo is on develop
     *
     * @return boolean
     */
    public function isOnDevelop()
    {
        return $this->isOnBranch('develop');
    }

    /**
     * Check if the repo os on a branch
     *
     * @param string $branch
     * @return boolean
     */
    public function isOnBranch($branch)
    {
        $this->output('Checking if repo is on ' . $branch);

        if (preg_match('/^On branch ([a-z]+)/', $this->getStatus(), $matches)) {

            return ($matches[1] === $branch);
        }

        return false;
    }

    /**
     * Checkout the latest develop
     */
    public function checkoutDevelop(): void
    {
        $this->output('Checking if we can check out develop');

        if (strstr($this->getStatus(), 'nothing to commit')) {

            $this->output('Checking out develop');

            shell_exec('cd ' . $this->getLocation() . ' && git checkout develop');
            $this->loadStatus();

        } else {

            throw new Exception($this->getName() . ' has uncommitted changes, please correct this and re-run the script');
        }
    }

    /**
     * Pull the latest develop
     */
    public function pullDevelop(): void
    {
        if (strstr($this->getStatus(), "Your branch is up-to-date with 'origin/develop'") === '' || strstr($this->getStatus(), "Your branch is up-to-date with 'origin/develop'") === '0' || !str_contains($this->getStatus(), "Your branch is up-to-date with 'origin/develop'")) {
            $this->output('Pulling latest develop');
            shell_exec('cd ' . $this->getLocation() . ' && git pull origin develop');
            $this->loadStatus();
        } else {
            $this->output('Up to date', Runner::MESSAGE_SUCCESS);
        }
    }

    /**
     * Create release branch
     */
    public function createRelease(): void
    {
        /**
        $this->output('Creating release ' . $this->getVersion());
        shell_exec('cd ' . $this->getLocation() . ' && git flow release start ' . $this->getVersion());
        $this->loadStatus();
         */

        $this->output('Merging into master');
        shell_exec('cd ' . $this->getLocation() . ' && git checkout master');
        $this->loadStatus();

        if (!$this->isOnBranch('master')) {
            throw new \Exception('Unable to checkout master');
        }

        shell_exec('cd ' . $this->getLocation() . ' && git merge develop');
        $this->loadStatus();
    }

    /**
     * Check if we have uncommited changes
     *
     * @return boolean
     */
    public function hasUncommittedChanges()
    {
        return strstr($this->getStatus(), 'nothing to commit') === '' || strstr($this->getStatus(), 'nothing to commit') === '0' || !str_contains($this->getStatus(), 'nothing to commit');
    }

    /**
     * Commit changes
     *
     * @param string $message
     */
    public function commitChanges($message): void
    {
        $this->output('Committing changes');

        shell_exec('cd ' . $this->getLocation() . ' && git add . && git commit -m "' . $message . '"');
    }

    /**
     * Publish repo
     */
    public function publish(): void
    {
        /**
        $this->output('Publishing release branch');

        shell_exec('cd ' . $this->getLocation() . ' && git flow release publish ' . $this->getVersion());
         */
        $this->output('Creating tag');

        shell_exec('cd ' . $this->getLocation() . ' && git tag ' . $this->getVersion() . ' && git push origin master && git push origin ' . $this->getVersion());
    }

    /**
     * Update the dependency versions in composer
     *
     * @param array $version
     */
    public function updateComposerJson(): void
    {
        $this->output('Looking for composer.json');

        $composerFile = $this->getLocation() . '/composer.json';
        if (file_exists($composerFile)) {

            $this->output('Updating composer.json');

            $composer = json_decode(file_get_contents($composerFile), true);

            if (isset($composer['repositories'])) {

                foreach ($composer['repositories'] as &$dependency) {

                    if (isset($dependency['package'])) {

                        $dependency['package']['version'] = $this->getVersion();
                        $dependency['package']['source']['reference'] = $this->getVersion();

                        $this->output('Updating dependency: ' . $dependency['package']['name']);
                    }
                }
            }

            if (isset($composer['require'])) {
                foreach ($composer['require'] as $key => &$require) {
                    if (preg_match('/olcs\/([a-zA-Z0-9\-]+)/', $key)) {
                        $require = (string)$this->getVersion();
                    }
                }
            }

            if (file_put_contents($composerFile, json_encode($composer, JSON_UNESCAPED_SLASHES)) === 0 || file_put_contents($composerFile, json_encode($composer, JSON_UNESCAPED_SLASHES)) === false) {

                throw new Exception($this->getName() . ': Could not write to ' . $composerFile);
            }

            $this->output('Composer updated', Runner::MESSAGE_SUCCESS);
        }

        $this->loadStatus();
    }

    /**
     * Output a message
     *
     * @param string $message
     *
     * @psalm-param '[32m'|'[34m' $type
     */
    private function output($message, string $type = Runner::MESSAGE_OK): void
    {
        $this->runner->output($this->getName() . ': ' . $message, $type);
    }
}

$release = new Runner();

$release->run();
