<?php

namespace Common\Filesystem;

/**
 * Wraps PHP shell functions
 *
 * @codeCoverageIgnore
 */
class Shell
{
    /**
     * Execute a system command
     *
     * @param string $command Command to execute
     * @param array  $output  Reference variable, if present will contain command output
     *
     * @return int $result
     */
    public function execute($command, &$output = null)
    {
        $output = null;
        $result = null;
        exec($command, $output, $result);

        return $result;
    }

    /**
     * Get file permissions
     *
     * @param string $file File
     *
     * @return int
     */
    public function fileperms($file)
    {
        return fileperms($file);
    }

    /**
     * Change file permissions
     *
     * @param string $file File
     * @param int    $mode Permissions
     *
     * @return bool
     */
    public function chmod($file, $mode)
    {
        $result = chmod($file, $mode);

        if ($result) {
            clearstatcache();
        }

        return $result;
    }
}
