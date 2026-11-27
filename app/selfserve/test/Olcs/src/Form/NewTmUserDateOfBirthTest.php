<?php

declare(strict_types=1);

namespace OlcsTest\Form;

use Common\InputFilter\PersonDateOfBirth;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\InputFilter\Factory;
use Laminas\Stdlib\ArrayUtils;
use Olcs\Form\Model\Form\Lva\NewTmUser;
use PHPUnit\Framework\TestCase;

final class NewTmUserDateOfBirthTest extends TestCase
{
    public function testBirthDateUsesRawPartsValidation(): void
    {
        $spec = ArrayUtils::iteratorToArray((new AttributeBuilder())->getFormSpecification(NewTmUser::class));
        $birthDate = $spec['input_filter']['data']['birthDate'];
        self::assertSame(PersonDateOfBirth::class, $birthDate['type']);

        $input = (new Factory())->createInput([
            'name' => 'birthDate',
            'type' => $birthDate['type'],
        ]);
        $input->setValue(['day' => '12', 'month' => '', 'year' => '1990']);
        self::assertFalse($input->isValid());
        self::assertSame(['dateIncomplete' => '{{fieldLabel}} must include a month'], $input->getMessages());
    }
}
