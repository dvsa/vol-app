<?php

namespace Cli\Session;

/**
 * NullSaveHandler, blocks he writing of session data
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class NullSaveHandler implements \Zend\Session\SaveHandler\SaveHandlerInterface
{
    /**
     * Open Session - retrieve resources
     *
     * @param string $savePath
     * @param string $name
     */
    public function open($savePath, $name)
    {
    }

    /**
     * Close Session - free resources
     *
     */
    public function close()
    {
    }

    /**
     * Read session data
     *
     * @param string $id
     */
    public function read($id)
    {
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id
     * @param mixed $data
     */
    public function write($id, $data)
    {
    }

    /**
     * Destroy Session - remove data from resource for
     * given session id
     *
     * @param string $id
     */
    public function destroy($id)
    {
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime
     */
    public function gc($maxlifetime)
    {
    }
}
