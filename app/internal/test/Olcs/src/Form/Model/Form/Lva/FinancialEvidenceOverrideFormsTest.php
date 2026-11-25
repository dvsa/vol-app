<?php

declare(strict_types=1);

namespace OlcsTest\Form\Model\Form\Lva;

use Common\Validator\ValidateIf;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Element\Textarea;
use Laminas\Form\FormInterface;
use Laminas\Validator\ValidatorChain;
use Olcs\Form\Model\Form\Lva\FinancialEvidenceIssueOverride;
use Olcs\Form\Model\Form\Lva\FinancialEvidenceOverrideRules as Rules;
use Olcs\Form\Model\Form\Lva\FinancialEvidencePassOverride;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Validates the two real override forms, built from their attributes. The controller tests use a
 * mocked form, so they cannot see what the forms themselves accept or say. Every rule the
 * caseworker can fall foul of lives in the forms (shared through FinancialEvidenceOverrideRules),
 * with its message, so it is all checked here, for both.
 */
final class FinancialEvidenceOverrideFormsTest extends TestCase
{
    /** Each form with an analyser flag its checks can genuinely have, other than skipped. */
    public static function formProvider(): \Iterator
    {
        yield 'issue, a fail' => [FinancialEvidenceIssueOverride::class, 'FAIL'];
        yield 'pass' => [FinancialEvidencePassOverride::class, 'PASS'];
    }

    /** What the caseworker can legitimately save. */
    #[DataProvider('acceptedProvider')]
    public function testAcceptsAValidAnswer(string $formClass, string $flag, string $answer, string $comment): void
    {
        $form = $this->form($formClass);
        $form->setData($this->data($flag, ['changeRecommendation' => $answer, 'comment' => $comment]));

        $this->assertTrue($form->isValid(), json_encode($form->getMessages()));
        $this->assertSame(trim($comment), $form->getData()['comment']);
    }

    public static function acceptedProvider(): \Iterator
    {
        foreach (self::formProvider() as $name => [$formClass, $flag]) {
            // "No" means the analyser's outcome stands, so it needs no text.
            yield "$name: no, nothing to say" => [$formClass, $flag, 'N', ''];
            yield "$name: no, with a note" => [$formClass, $flag, 'N', ' a note '];
            yield "$name: yes, with a reason" => [$formClass, $flag, 'Y', 'Reconsidered'];
            yield "$name: yes, at the length limit" => [$formClass, $flag, 'Y', str_repeat('a', 1000)];
        }

        // A skipped check was never assessed, so there is no outcome to overturn: no reason needed.
        yield 'issue, skipped: yes, nothing to say' => [FinancialEvidenceIssueOverride::class, 'SKIPPED', 'Y', ''];
    }

    /**
     * Everything the caseworker can get wrong is caught here, by the form, with a message worded
     * for them against the field it is about. The controller shows the form's own messages; it
     * has no validation rules of its own.
     */
    #[DataProvider('caseworkerErrorProvider')]
    public function testSaysWhatTheCaseworkerNeedsToPutRight(
        string $formClass,
        string $flag,
        array $data,
        string $field,
        string $message
    ): void {
        $form = $this->form($formClass);
        $form->setData($this->data($flag, $data));

        $this->assertFalse($form->isValid());
        $this->assertSame([$field], array_keys($form->getMessages()));
        $this->assertSame([$message], array_values($form->getMessages()[$field]));
    }

    public static function caseworkerErrorProvider(): \Iterator
    {
        foreach (self::formProvider() as $name => [$formClass, $flag]) {
            yield "$name: no answer" => [$formClass, $flag, ['changeRecommendation' => ''], 'changeRecommendation', Rules::MSG_ANSWER_REQUIRED];
            // Overturning a genuine pass or fail always needs a reason.
            yield "$name: yes, no reason" => [$formClass, $flag, ['comment' => ''], 'comment', Rules::MSG_REASON_REQUIRED];
            yield "$name: yes, only spaces" => [$formClass, $flag, ['comment' => '   '], 'comment', Rules::MSG_REASON_REQUIRED];
            yield "$name: yes, too long" => [$formClass, $flag, ['comment' => str_repeat('a', 1001)], 'comment', Rules::MSG_COMMENT_TOO_LONG];
            yield "$name: no, note too long" => [
                $formClass,
                $flag,
                ['changeRecommendation' => 'N', 'comment' => str_repeat('a', 1001)],
                'comment',
                Rules::MSG_COMMENT_TOO_LONG,
            ];
        }

        // The length limit applies to a skipped check too.
        yield 'issue, skipped: yes, too long' => [
            FinancialEvidenceIssueOverride::class,
            'SKIPPED',
            ['comment' => str_repeat('a', 1001)],
            'comment',
            Rules::MSG_COMMENT_TOO_LONG,
        ];
    }

    /** Hidden fields the caseworker never types; a bad value here is a tampered or broken form. */
    #[DataProvider('brokenFormProvider')]
    public function testRejectsABrokenForm(string $formClass, array $data, string $field): void
    {
        $form = $this->form($formClass);
        $form->setData($this->data('FAIL', $data));

        $this->assertFalse($form->isValid());
        $this->assertArrayHasKey($field, $form->getMessages());
    }

    public static function brokenFormProvider(): \Iterator
    {
        foreach ([FinancialEvidenceIssueOverride::class, FinancialEvidencePassOverride::class] as $formClass) {
            yield "$formClass: an analysis id that is not a number" => [$formClass, ['analysisId' => 'x'], 'analysisId'];
            // Bank carries no flag, so it is not a check that can be changed.
            yield "$formClass: a row that is not a check" => [$formClass, ['row' => 'bank'], 'row'];
        }
    }

    /** A valid "Yes" with a reason, as the controller gives it the form, with $overrides applied. */
    private function data(string $analyserFlag, array $overrides): array
    {
        return $overrides + [
            'analysisId' => '9',
            'row' => 'name',
            'changeRecommendation' => 'Y',
            'comment' => 'x',
            Rules::CONTEXT_ANALYSER_FLAG => $analyserFlag,
        ];
    }

    private function form(string $formClass): FormInterface
    {
        $builder = new AttributeBuilder();

        // The application registers these aliases; a bare builder needs telling.
        $elements = $builder->getFormFactory()->getFormElementManager();
        $elements->setAllowOverride(true);
        $elements->setAlias('TextArea', Textarea::class);

        $chain = new ValidatorChain();
        $validators = $chain->getPluginManager();
        $validators->setInvokableClass(ValidateIf::class);
        $validators->setAlias('ValidateIf', ValidateIf::class);
        $builder->getFormFactory()->getInputFilterFactory()->setDefaultValidatorChain($chain);

        return $builder->createForm($formClass);
    }
}
