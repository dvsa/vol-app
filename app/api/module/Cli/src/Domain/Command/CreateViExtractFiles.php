<?php

/**
 * Extract VI files
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Cli\Domain\Command;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Extract VI files
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateViExtractFiles extends AbstractCommand
{
    protected $vhl = null;

    protected $tnm = null;

    protected $op = null;

    protected $oc = null;

    protected $all = null;

    protected $path = null;

    /**
     * @return mixed
     */
    public function getVhl()
    {
        return $this->vhl;
    }

    /**
     * @return mixed
     */
    public function getTnm()
    {
        return $this->tnm;
    }

    /**
     * @return mixed
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * @return mixed
     */
    public function getOc()
    {
        return $this->oc;
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        return $this->all;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }
}
