<?php

namespace Dvsa\OlcsTest\Transfer\Command\Cases\ProposeToRevoke;

use Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke\UpdateProposeToRevoke;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;

class UpdateProposeToRevokeTest extends \PHPUnit\Framework\TestCase
{
    use CommandTest;

    protected function createBlankDto()
    {
        return new UpdateProposeToRevoke();
    }

    protected function getOptionalDtoFields()
    {
        return ['assignedCaseworker'];
    }

    protected function getValidFieldValues()
    {
        return [
            'assignedCaseworker' => ['1', '2', '9999'],
        ];
    }

    protected function getInvalidFieldValues()
    {
        return [
            'assignedCaseworker' => [['unexpected' => 'array']],
        ];
    }

    protected function getFilterTransformations()
    {
        return [
            'assignedCaseworker' => [
                ['a1b2c3', '123'],
                [true, '1'],
                [99, '99'],
            ]
        ];
    }
}
