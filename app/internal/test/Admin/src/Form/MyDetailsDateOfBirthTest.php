<?php

declare(strict_types=1);

namespace AdminTest\Form;

use Admin\Form\Model\Form\MyDetails;
use Common\Filter\DateSelectNullifier;
use Common\Form\Elements\Custom\DateSelect;
use Laminas\Filter\FilterPluginManager;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Factory;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MyDetailsDateOfBirthTest extends TestCase
{
    public function testOptionalBirthDateRejectsPartialDateAndPreservesValues(): void
    {
        $form = $this->createBirthDateForm();
        $date = ['day' => '12', 'month' => '', 'year' => '1990'];
        $form->setData(['person' => ['birthDate' => $date]]);

        self::assertFalse($form->isValid());
        self::assertSame(
            ['dateIncomplete' => '{{fieldLabel}} must include a month'],
            $form->getMessages()['person']['birthDate']
        );
        $element = $form->get('person')->get('birthDate');
        self::assertSame('12', $element->getDayElement()->getValue());
        self::assertSame('', $element->getMonthElement()->getValue());
        self::assertSame('1990', $element->getYearElement()->getValue());
    }

    public function testOptionalBirthDateAcceptsBlank(): void
    {
        $form = $this->createBirthDateForm();
        $form->setData(['person' => ['birthDate' => ['day' => '', 'month' => '', 'year' => '']]]);

        self::assertTrue($form->isValid());
        self::assertNull($form->getData()['person']['birthDate']);
    }

    #[DataProvider('otherInternalDobFields')]
    public function testOtherInternalDobFieldsUseRawParts(
        string $formClass,
        string $fieldset,
        string $inputClass
    ): void {
        $spec = ArrayUtils::iteratorToArray((new AttributeBuilder())->getFormSpecification($formClass));
        $birthDate = $spec['input_filter'][$fieldset]['birthDate'];
        self::assertSame($inputClass, $birthDate['type']);

        $input = (new \Laminas\InputFilter\Factory())->createInput([
            'name' => 'birthDate',
            'type' => $birthDate['type'],
            'required' => $birthDate['required'] ?? true,
        ]);
        $input->setValue(['day' => '12', 'month' => '', 'year' => '1990']);
        self::assertFalse($input->isValid(['defendantType' => 'def_t_dr']));
        self::assertSame(['dateIncomplete' => '{{fieldLabel}} must include a month'], $input->getMessages());
    }

    public static function otherInternalDobFields(): array
    {
        return [
            'user management' => [\Admin\Form\Model\Form\User::class, 'userPersonal', \Common\InputFilter\PersonDateOfBirth::class],
            'transport manager' => [\Olcs\Form\Model\Form\TransportManager::class, 'transport-manager-details', \Common\InputFilter\PersonDateOfBirth::class],
            'conviction' => [\Olcs\Form\Model\Form\Conviction::class, 'fields', \Common\InputFilter\OffenceDateOfBirth::class],
            'search' => [\Olcs\Form\Model\Form\Search::class, 'search', \Common\InputFilter\PersonDateOfBirth::class],
        ];
    }

    private function createBirthDateForm(): \Laminas\Form\FormInterface
    {
        $spec = ArrayUtils::iteratorToArray((new AttributeBuilder())->getFormSpecification(MyDetails::class));
        $fieldsets = array_values(array_filter(
            $spec['fieldsets'],
            static fn(array $fieldset): bool => $fieldset['spec']['name'] === 'person'
        ));
        $fieldsets[0]['spec']['elements'] = array_values(array_filter(
            $fieldsets[0]['spec']['elements'],
            static fn(array $element): bool => $element['spec']['name'] === 'birthDate'
        ));
        $spec['fieldsets'] = $fieldsets;
        $spec['input_filter'] = [
            'person' => [
                'birthDate' => $spec['input_filter']['person']['birthDate'],
                'type' => $spec['input_filter']['person']['type'],
            ],
        ];

        $elements = new FormElementManager(new ServiceManager());
        $elements->setAlias('DateSelect', DateSelect::class);
        $elements->setInvokableClass(DateSelect::class, DateSelect::class);
        $factory = new Factory($elements);
        $filters = new FilterPluginManager(new ServiceManager());
        $filters->setAlias('DateSelectNullifier', DateSelectNullifier::class);
        $filters->setInvokableClass(DateSelectNullifier::class, DateSelectNullifier::class);
        $factory->getInputFilterFactory()->getDefaultFilterChain()->setPluginManager($filters);

        return $factory->createForm($spec);
    }
}
