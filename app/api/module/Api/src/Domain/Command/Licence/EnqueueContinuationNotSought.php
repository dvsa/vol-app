<?php

namespace Dvsa\Olcs\Api\Domain\Command\Licence;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Enqueue CNS jobs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class EnqueueContinuationNotSought extends AbstractCommand
{
    /**
     * @var array
     */
    protected $licences;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * Get licences
     *
     * @return array
     */
    public function getLicences()
    {
        return $this->licences;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}
