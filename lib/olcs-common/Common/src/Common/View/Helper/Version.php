<?php

declare(strict_types=1);

namespace Common\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Version view helper
 *
 * Conditionally renders application version information including environment,
 * PHP version, description, and release details based on the
 * ['version']['show_information_bar'] configuration setting.
 */
class Version extends AbstractHelper
{
    public const TEMPLATE_PATH = 'helper/version.phtml';
    public const DEFAULT_UNDEFINED = 'undefined';
    public const DEFAULT_EMPTY = 'empty';

    private bool $shouldRender = false;
    private string $environment = self::DEFAULT_UNDEFINED;
    private string $description = self::DEFAULT_UNDEFINED;
    private string $release = self::DEFAULT_UNDEFINED;

    /**
     * Create service instance
     */
    public function __construct(array $config)
    {
        // Check if version config exists and is valid
        if (!isset($config['version']) || !is_array($config['version'])) {
            return;
        }

        // Check if information bar should be displayed
        $this->shouldRender = $config['version']['show_information_bar'] ?? false;

        // Pre-calculate version values if rendering is enabled
        if ($this->shouldRender) {
            $this->environment = $config['version']['environment'] ?? self::DEFAULT_UNDEFINED;
            $this->description = $config['version']['description'] ?? self::DEFAULT_UNDEFINED;
            $this->release = $config['version']['release'] ?? self::DEFAULT_UNDEFINED;

            // Handle explicitly set empty strings
            if ($this->environment === '') {
                $this->environment = self::DEFAULT_EMPTY;
            }
            if ($this->description === '') {
                $this->description = self::DEFAULT_EMPTY;
            }
            if ($this->release === '') {
                $this->release = self::DEFAULT_EMPTY;
            }
        }
    }

    /**
     * Invoke the view helper
     *
     * @return string
     * @see render()
     */
    public function __invoke(): string
    {
        return $this->render();
    }

    /**
     * Render the version information bar
     *
     * Returns formatted HTML containing environment, PHP version, description,
     * and release information if show_information_bar is enabled in config.
     * Returns empty string if rendering is disabled or config is invalid.
     *
     * @return string HTML markup or empty string
     */
    public function render(): string
    {
        if (!$this->shouldRender) {
            return '';
        }

        return $this->getView()->render(self::TEMPLATE_PATH, [
            'environment' => $this->environment,
            'phpVersion' => phpversion(),
            'description' => $this->description,
            'release' => $this->release
        ]);
    }
}
