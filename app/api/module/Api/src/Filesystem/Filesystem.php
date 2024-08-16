<?php

namespace Dvsa\Olcs\Api\Filesystem;

use Symfony\Component\Filesystem\Filesystem as BaseFileSystem;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;

/**
 * Class Filesystem
 * @package Dvsa\Olcs\Api\Filesystem
 */
class Filesystem extends BaseFileSystem
{
    public const LOCK_TRIES = 3;

    /**
     * @param $path
     * @param string $prefix
     * @param bool $cleanup
     * @return string
     */
    public function createTmpDir($path, string $prefix = '', bool $cleanup = true)
    {
        $lock = $this->getLock($path);
        $lock->acquire(true);

        do {
            $dirname = $path . DIRECTORY_SEPARATOR . uniqid($prefix);
        } while ($this->exists($dirname));

        $this->mkdir($dirname, 0750);

        $lock->release();

        if ($cleanup) {
            $this->cleanupTmp($dirname);
        }

        return $dirname;
    }

    /**
     * @param $path
     * @param string $prefix
     * @param bool $cleanup
     * @return string
     */
    public function createTmpFile($path, $prefix = '', $cleanup = true)
    {
        $lock = $this->getLock($path);

        // sometimes we are getting error trying to lock the file on pre-prod
        // if the fix below will not work we can try to put new LockHandler inside the try/catch block
        $tryToLock = 0;

        do {
            try {
                $lock->acquire(true);
            } catch (LockConflictedException | LockAcquiringException $exception) {
                if ($tryToLock === self::LOCK_TRIES) {
                    throw $exception;
                }
            }
            usleep(500);
        } while (!$lock->isAcquired() && $tryToLock++ < self::LOCK_TRIES);

        do {
            $filename = $path . DIRECTORY_SEPARATOR . uniqid($prefix);
        } while ($this->exists($filename));

        $this->touch($filename);

        $lock->release();

        if ($cleanup) {
            $this->cleanupTmp($filename);
        }

        return $filename;
    }

    /**
     * @param $name
     */
    private function cleanupTmp($name)
    {
        register_shutdown_function(
            function () use ($name) {
                $this->remove($name);
            }
        );
    }

    protected function getLock($path): LockInterface
    {
        $store = new FlockStore();
        $factory = new LockFactory($store);
        return $factory->createLock($path);
    }
}
