<?php

namespace Olcs\Filesystem;

use Symfony\Component\Filesystem\Filesystem as BaseFileSystem;
use Symfony\Component\Filesystem\LockHandler;

/**
 * Class Filesystem
 * @package Olcs\Filesystem
 */
class Filesystem extends BaseFileSystem
{
    /**
     * @param $path
     * @param string $prefix
     * @return string
     */
    public function createTmpDir($path, $prefix = '')
    {
        $lock = new LockHandler(hash('sha256', $path));
        $lock->lock(true);

        do {
            $dirname = $path . DIRECTORY_SEPARATOR . uniqid($prefix);
        } while ($this->exists($dirname));

        $this->mkdir($dirname);

        $lock->release();

        return $dirname;
    }
}
