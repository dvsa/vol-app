<?php

namespace OlcsTest\Controller\Traits;

/**
 * Class DeleteActionTraitTest
 * @package OlcsTest\Controller\Traits
 */
class DeleteActionTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testDeleteAction()
    {
        $mockBuilder = $this->getMockBuilder('Olcs\Controller\Traits\DeleteActionTrait');
        $mockBuilder->setMethods(['params', 'makeRestCall', 'redirectToIndex', 'getDeleteServiceName', 'addErrorMessage']);
        $mockBuilder->setMockClassName(uniqid('mock_DeleteActionTrait_'));
        $sut = $mockBuilder->getMockForTrait();

        $mockParams = $this->getMock('stdClass', ['fromRoute']);
        $mockParams->expects($this->once())->method('fromRoute')->with($this->equalTo('id'))->willReturn(27);

        $sut->expects($this->once())->method('params')->willReturn($mockParams);
        $sut->expects($this->once())->method('getDeleteServiceName')->willReturn('test');
        $sut->expects($this->once())->method('addErrorMessage')->with('Deleted sucessfully');
        $sut->expects($this->once())->method('redirectToIndex')->willReturn($this->returnValue(null));
        $sut->expects($this->once())
            ->method('makeRestCall')
            ->with($this->equalTo('test'), $this->equalTo('DELETE'), $this->equalTo(['id' => 27]));

        $sut->expects($this->once())->method('redirectToIndex');

        $sut->deleteAction();
    }

    public function testGetIdentifier()
    {
        $mockBuilder = $this->getMockBuilder('Olcs\Controller\Traits\DeleteActionTrait');
        $mockBuilder->setMethods(['params', 'makeRestCall', 'redirectToIndex', 'getDeleteServiceName']);
        $mockBuilder->setMockClassName(uniqid('mock_DeleteActionTrait_'));
        $sut = $mockBuilder->getMockForTrait();

        $sut->identifierName = 'identifierName';

        $this->assertEquals('identifierName', $sut->getIdentifierName());
    }
}
