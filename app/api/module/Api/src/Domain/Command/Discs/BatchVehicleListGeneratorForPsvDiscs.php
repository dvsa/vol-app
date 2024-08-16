<?php

/**
 * Batch Vehicle List Generator for Psv Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Discs;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Batch Vehicle List Generator for Psv Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class BatchVehicleListGeneratorForPsvDiscs extends AbstractCommand
{
    protected $bookmarks = [];

    protected $queries = [];

    protected $user;

    /**
     * @return mixed
     */
    public function getBookmarks()
    {
        return $this->bookmarks;
    }

    /**
     * @return mixed
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}
