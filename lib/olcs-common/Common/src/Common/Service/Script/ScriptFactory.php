<?php

namespace Common\Service\Script;

use Laminas\View\HelperPluginManager;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Inline JavaScript loading service
 */
class ScriptFactory implements FactoryInterface
{
    /**
     * Hold the application configuration
     *
     * @var array
     */
    protected $filePaths = [];

    /**
     * Hold an array of tokens we'll search for and replace in the
     * loaded script file. This is currently not used but may be
     * in future
     */
    protected $tokens = [];

    /**
     * Contains the view helper manager! :)
     *
     * @var HelperPluginManager
     */
    protected $viewHelperManager;

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->setViewHelperManager($container->get('ViewHelperManager'));

        $config = $container->get('Config');

        if (!isset($config['local_scripts_path'])) {
            throw new \LogicException('local_scripts_path was not set in the module config');
        }

        $this->setFilePaths($config['local_scripts_path']);

        return $this;
    }

    /**
     * load an array of files
     *
     * @param array $files - the files to load
     */
    public function loadFiles($files = []): void
    {
        foreach ($files as $file) {
            $this->loadFile($file);
        }
    }

    /**
     * add an array of files
     *
     * @param array $files - the files to load
     */
    public function appendFiles($files = []): void
    {
        foreach ($files as $file) {
            $this->appendFile($file);
        }
    }

    /**
     * load a single file, will check multiple paths depending on the number of available modules
     *
     * @param string $file - the file to load
     * @throws \Exception
     */
    public function loadFile($file): void
    {
        $paths = $this->getFilePaths();

        if (is_array($paths)) {
            foreach ($this->getFilePaths() as $path) {
                $fullPath = $path . $file . '.js';

                if ($this->exists($fullPath)) {
                    $data = $this->load($fullPath);
                    $this->getViewHelperManager()->get('inlineScript')->appendScript(
                        $this->replaceTokens($data, $this->tokens)
                    );
                    return;
                }
            }
        }

        throw new \Exception('Attempted to load invalid script file "' . $file . '"');
    }

    /**
     * Append a single file, will check multiple paths depending on the number of available modules
     *
     * @param string $file - the file to load
     */
    public function appendFile($fileName): void
    {
        $assetPath = $this->getViewHelperManager()->get('assetPath');
        $this->getViewHelperManager()->get('inlineScript')->appendFile($assetPath($fileName));
    }

    /**
     * check to see if a file exists
     *
     * @param string file - the file to check
     *
     * @return bool
     */
    protected function exists($file)
    {
        return file_exists($file);
    }

    /**
     * load the data from a file
     *
     * @param string $file - the file to load
     *
     * @return string
     */
    protected function load($file)
    {
        return file_get_contents($file);
    }

    /**
     * replace any {{tokens}} found in the content string
     * currently a no-op identity method; may be used in future
     *
     * @param string $content - the string of content to search through
     * @param array  $tokens  - the array of tokens to search for and replace
     *
     * @return string
     */
    protected function replaceTokens($content, $tokens)
    {
        // no-op at the moment
        return $content;
    }

    /**
     * get the available file system paths across all modules
     *
     * @return array
     */
    protected function getFilePaths()
    {
        return $this->filePaths;
    }

    public function setFilePaths($filePaths): static
    {
        $this->filePaths = $filePaths;
        return $this;
    }

    public function getViewHelperManager(): HelperPluginManager
    {
        return $this->viewHelperManager;
    }

    public function setViewHelperManager(\Laminas\View\HelperPluginManager $viewHelperManager): static
    {
        $this->viewHelperManager = $viewHelperManager;
        return $this;
    }
}
