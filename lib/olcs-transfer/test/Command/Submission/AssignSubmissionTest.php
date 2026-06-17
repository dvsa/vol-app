<?php

namespace Dvsa\OlcsTest\Transfer\Command\Operator;

use Dvsa\Olcs\Transfer\Command\Submission\AssignSubmission;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;

class AssignSubmissionTest extends \PHPUnit\Framework\TestCase
{
    use CommandTest;

    protected function createBlankDto()
    {
        return new AssignSubmission();
    }

    protected function getOptionalDtoFields()
    {
        return [];
    }

    protected function getValidFieldValues()
    {
        return [];
    }

    protected function getInvalidFieldValues()
    {
        return [];
    }


    protected function getFilterTransformations()
    {

        return [
            'recipientUser' => [99, '99'],
            'presidingTcUser' => [97, '97'],
            'urgent' => [' Y', 'Y']
        ];
    }
}
