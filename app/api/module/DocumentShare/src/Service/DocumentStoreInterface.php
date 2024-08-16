<?php

namespace Dvsa\Olcs\DocumentShare\Service;

use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Exception;
use Laminas\Http\Response;

interface DocumentStoreInterface
{
    /**
     * Store file on remote storage
     *
     * @param string $path File Path on storage
     * @param File   $file File
     *
     * @return mixed
     * @throws Exception
     */
    public function write($path, File $file);

    /**
     * Remove file on storage
     *
     * @param string $path Path to file on storage
     *
     * @param bool   $hard
     *
     * @return mixed
     */
    public function remove($path, $hard = false);

    /**
     * Read content from document store
     *
     * @param string $path Path
     *
     * @return mixed
     */
    public function read($path);
}
