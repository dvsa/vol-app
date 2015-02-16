<?php

/**
 * SubmissionSections Element Tests
 *
 * @author shaun.lizzio@valtech.co.uk>
 */
namespace OlcsTest\Form\Element;

use PHPUnit_Framework_TestCase;
use Olcs\Form\Element\SubmissionSections;
use Mockery as m;

/**
 * SubmissionSections Element Tests
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class SubmissionSectionsTest extends PHPUnit_Framework_TestCase
{
    public function testGetInputSpecification()
    {
        $sut = new SubmissionSections();
        $this->assertInternalType('array', $sut->getInputSpecification());
        $this->assertArrayHasKey('name', $sut->getInputSpecification());
        $this->assertArrayHasKey('filters', $sut->getInputSpecification());
    }


    /**
     * @dataProvider getSubmissionSectionsProvider
     */
    public function testSetValue($submissionType, $sections)
    {
        $data = ['submissionType' => $submissionType, 'sections' => $sections];
        $sut = new SubmissionSections();

        $mockSelect = m::mock('Zend\Form\Element\Select');
        $mockSelect->shouldReceive('setValue')->with($data['submissionType']);
        $sut->setSubmissionType($mockSelect);

        $mockMultiCheckbox = m::mock('Zend\Form\Element\MultiCheckbox');
        $mockMultiCheckbox->shouldReceive('setValue')->with($data['sections']);
        $sut->setSections($mockMultiCheckbox);

        $sut->setValue($data);

        $this->assertEquals($sut->getSubmissionType(), $mockSelect);
        $this->assertEquals($sut->getSections(), $mockMultiCheckbox);
        $this->assertNotEmpty($sut->getSections());
    }

    /**
     * @dataProvider getSubmissionSectionsProvider
     */
    public function testPrepareElement($submissionType, $sections)
    {
        $name = 'test';
        $data = ['submissionType' => $submissionType, 'sections' => $sections];
        $sut = new SubmissionSections();

        $mockSelect = m::mock('Zend\Form\Element\Select');
        $mockSelect->shouldReceive('setName')->with($name . '[submissionType]');

        $sut->setSubmissionType($mockSelect);

        $mockMultiCheckbox = m::mock('Zend\Form\Element\MultiCheckbox');
        $mockMultiCheckbox->shouldReceive(
            'getValueOptions'
        )->andReturn(
            [
                'case-summary' => 'Case Summary',
                'introduction' => 'Case Introduction',
                'persons' => 'Persons',
                'case-outline' => 'Cases',
                'outstanding-applications' => 'Outstanding applications'
            ]
        );
        $mockMultiCheckbox->shouldReceive('setValueOptions');
        $mockMultiCheckbox->shouldReceive('setOptions')->with(['label_position'=>'append']);
        $mockMultiCheckbox->shouldReceive('setName')->with($name . '[sections]');

        $sut->setSections($mockMultiCheckbox);

        $mockSubmitButton = m::mock('Zend\Form\Element\Button');
        $mockSubmitButton->shouldReceive('setName')->with($name . '[submissionTypeSubmit]');

        $sut->setSubmissionTypeSubmit($mockSubmitButton);

        $sut->setName($name);

        $mockForm = m::mock('Zend\Form\Form');

        $sut->prepareElement($mockForm);

        $this->assertNotEmpty($sut->getSections());
        $this->assertNotEmpty($sut->getSubmissionType());

    }

    public function getSubmissionSectionsProvider()
    {
        return array(
            array(
                'sub_type1',
                array(
                    'section 1',
                    'section 2'
                )
            ),
            array(
                'sub_type2',
                array()
            )
        );
    }
}
