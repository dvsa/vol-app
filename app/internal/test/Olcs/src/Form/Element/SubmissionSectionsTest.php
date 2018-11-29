<?php

/**
 * SubmissionSections Element Tests
 *
 * @author shaun.lizzio@valtech.co.uk>
 */
namespace OlcsTest\Form\Element;

use Olcs\Form\Element\SubmissionSections;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;

/**
 * SubmissionSections Element Tests
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class SubmissionSectionsTest extends TestCase
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
    public function testSetValue($submissionType, $submissionTypeSubmit, $sections)
    {
        $data = [
            'submissionType' => $submissionType,
            'submissionTypeSubmit' => $submissionTypeSubmit,
            'sections' => $sections
        ];
        $sut = new SubmissionSections();

        $mockSelect = m::mock('Zend\Form\Element\Select');
        $mockSelect->shouldReceive('setValue')->with($data['submissionType']);
        $sut->setSubmissionType($mockSelect);

        $mockMultiCheckbox = m::mock('Zend\Form\Element\MultiCheckbox');
        $mockMultiCheckbox->shouldReceive('setValue')->with(m::type('array'));
        $mockMultiCheckbox->shouldReceive('getValueOptions')->andReturn(['operating-centres' => 'Operating centres']);
        $mockMultiCheckbox->shouldReceive('setValueOptions');

        $sut->setSections($mockMultiCheckbox);

        $sut->setValue($data);

        $this->assertEquals($sut->getSubmissionType(), $mockSelect);
        $this->assertEquals($sut->getSections(), $mockMultiCheckbox);
        $this->assertNotEmpty($sut->getSections());
    }

    /**
     * Tests prepare submissionSections element for Non-TM
     */
    public function testPrepareElementNonTm()
    {

        $name = 'test';

        $sut = new SubmissionSections();

        $mockSelect = m::mock('Zend\Form\Element\Select');
        $mockSelect->shouldReceive('setName')->with($name . '[submissionType]');

        $sut->setSubmissionType($mockSelect);

        $mockTm = m::mock('Zend\Form\Element\Hidden');
        $mockTm->shouldReceive('setName')->with($name . '[transportManager]');
        $mockTm->shouldReceive('getValue')->andReturnNull();

        $sut->setTransportManager($mockTm);

        $mockMultiCheckbox = m::mock('Zend\Form\Element\MultiCheckbox');
        $mockMultiCheckbox->shouldReceive(
            'getValueOptions'
        )->andReturn(
            [
                'case-summary' => 'Case Summary',
                'introduction' => 'Case Introduction',
                'people' => 'People',
                'case-outline' => 'Cases',
                'most-serious-infringement' => 'Most serious infringment',
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

        $mockForm = new \Zend\Form\Form();

        $sut->prepareElement($mockForm);

        $this->assertNotEmpty($sut->getSections());
        $this->assertNotEmpty($sut->getSubmissionType());
    }

    /**
     * Tests prepare submissionSections element for TM
     */
    public function testPrepareElementForTm()
    {
        $name = 'test';
        $transportManagerId = 3;

        $sut = new SubmissionSections();

        $mockSelect = m::mock('Zend\Form\Element\Select');
        $mockSelect->shouldReceive('setName')->with($name . '[submissionType]');

        $sut->setSubmissionType($mockSelect);

        $mockTm = m::mock('Zend\Form\Element\Hidden');
        $mockTm->shouldReceive('setName')->with($name . '[transportManager]');
        $mockTm->shouldReceive('getValue')->andReturn($transportManagerId);

        $sut->setTransportManager($mockTm);

        $mockMultiCheckbox = m::mock('Zend\Form\Element\MultiCheckbox');
        $mockMultiCheckbox->shouldReceive(
            'getValueOptions'
        )->andReturn(
            [
                'case-summary' => 'Case Summary',
                'introduction' => 'Case Introduction',
                'people' => 'People',
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

        $mockForm = new \Zend\Form\Form();

        $sut->prepareElement($mockForm);

        $this->assertNotEmpty($sut->getSections());
        $this->assertNotEmpty($sut->getSubmissionType());
    }

    public function getSubmissionSectionsProvider()
    {
        return array(
            array(
                'sub_type1',
                null,
                array(
                    'section 1',
                    'section 2'
                )
            ),
            array(
                'sub_type2',
                null,
                array()
            ),
            array(
                'submission_type_o_tm',
                null,
                null
            ),
            array(
                'submission_type_o_bus_reg',
                'pressed',
                array(
                    'operating-centres'
                )
            ),
            array(
                'submission_type_o_clo_fep',
                'pressed',
                array(
                    'waive-fee-late-fee'
                )
            ),
            array(
                'submission_type_o_clo_g',
                'pressed',
                array(
                    'operating-centres'
                )
            ),
            array(
                'submission_type_o_clo_psv',
                'pressed',
                array(
                    'operating-centres'
                )
            ),
            array(
                'submission_type_o_env',
                'pressed',
                array(
                    'operating-centres'
                )
            ),
            array(
                'submission_type_o_irfo',
                'pressed',
                array(
                    'operating-centres'
                )
            ),
            array(
                'submission_type_o_mlh_clo',
                'pressed',
                array(
                    'operating-centres'
                )
            ),
            array(
                'submission_type_o_mlh_otc',
                'pressed',
                array(
                    'operating-centres'
                )
            ),
            array(
                'submission_type_o_otc',
                'pressed',
                array(
                    'operating-centres'
                )
            ),
            array(
                'submission_type_o_tm',
                'pressed',
                array(
                    'operating-centres'
                )
            ),
            array(
                'submission_type_o_schedule_41',
                'pressed',
                array(
                    'operating-centres'
                )
            ),
            array(
                'submission_type_o_impounding',
                'pressed',
                array(
                    'statements'
                )
            ),
            array(
                'submission_type_o_ni_tru',
                'pressed',
                array(
                    'statements'
                )
            ),
            array(
                'UNKNOWN_submission_type',
                'pressed',
                array(
                    'statements'
                )
            )
        );
    }
}
