<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\EmailAddress;
use Dvsa\Olcs\Transfer\Validators\ValidateEach;
use Laminas\Validator\Digits;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\IsCountable;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

class ValidateEachTest extends TestCase
{
    public function testConstructorThrowsExceptionWhenNoChildrenAreSet()
    {
        // Set Expectations
        $this->expectException(\Laminas\Validator\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid option 'children'");

        // Execute
        new ValidateEach();
    }

    /**
     * @depends testConstructorThrowsExceptionWhenNoChildrenAreSet
     */
    public function testConstructorThrowsExceptionWhenNoChildrenAreEmpty()
    {
        // Set Expectations
        $this->expectException(\Laminas\Validator\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid option 'children': option should not be empty");

        // Execute
        new ValidateEach(['children' => []]);
    }

    public function testIsValidator()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class]]]);

        // Assert
        $this->assertInstanceOf(ValidatorInterface::class, $validator);
    }

    /**
     * @depends testIsValidator
     */
    public function testIsValidValidatesASingleValidValueAgainstAChildValidatorAndReturnsTrue()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class]]]);

        // Execute
        $result = $validator->isValid(['1234']);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @depends testIsValidValidatesASingleValidValueAgainstAChildValidatorAndReturnsTrue
     */
    public function testIsValidValidatesASingleValidValueAgainstAChildValidatorAndSetsNoMessages()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class]]]);

        // Execute
        $validator->isValid(['1234']);

        // Assert
        $this->assertEmpty($validator->getMessages());
    }

    /**
     * @depends testIsValidValidatesASingleValidValueAgainstAChildValidatorAndReturnsTrue
     */
    public function testIsValidValidatesMultipleValidValuesAgainstAChildValidatorAndReturnsTrue()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class]]]);

        // Execute
        $result = $validator->isValid(['1234', 1]);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @depends testIsValidValidatesMultipleValidValuesAgainstAChildValidatorAndReturnsTrue
     * @depends testIsValidValidatesASingleValidValueAgainstAChildValidatorAndSetsNoMessages
     */
    public function testIsValidValidatesMultipleValidValuesAgainstAChildValidatorAndSetsNoMessages()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class]]]);

        // Execute
        $validator->isValid(['1234', 1]);

        // Assert
        $this->assertEmpty($validator->getMessages());
    }

    /**
     * @depends testIsValidator
     */
    public function testIsValidValidatesASingleInvalidValueAgainstAChildValidatorAndReturnsFalse()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class]]]);

        // Execute
        $result = $validator->isValid(['12a34']);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @depends testIsValidValidatesASingleInvalidValueAgainstAChildValidatorAndReturnsFalse
     * @depends testIsValidValidatesASingleValidValueAgainstAChildValidatorAndSetsNoMessages
     */
    public function testIsValidValidatesASingleInvalidValueAgainstAChildValidatorAndSetsMessages()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class]]]);

        // Execute
        $validator->isValid(['12a34']);

        // Assert
        $this->assertCount(1, $validator->getMessages());
    }

    /**
     * @depends testIsValidValidatesASingleInvalidValueAgainstAChildValidatorAndReturnsFalse
     */
    public function testIsValidValidatesMultipleInvalidValuesAgainstAChildValidatorAndReturnsFalse()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class]]]);

        // Execute
        $result = $validator->isValid(['12a34', 'a']);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @depends testIsValidValidatesMultipleInvalidValuesAgainstAChildValidatorAndReturnsFalse
     * @depends testIsValidValidatesASingleInvalidValueAgainstAChildValidatorAndSetsMessages
     */
    public function testIsValidValidatesMultipleInvalidValuesAgainstAChildValidatorAndSetsMessages()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class]]]);

        // Execute
        $validator->isValid(['12a34', 'a']);

        // Assert
        $this->assertCount(2, $validator->getMessages());
    }

    /**
     * @depends testIsValidValidatesASingleInvalidValueAgainstAChildValidatorAndSetsMessages
     */
    public function testIsValidSetsMessagesWithKeysEqualToTheIndexOfTheInvalidArrayItemAndTheOriginalValidatorKey()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class]]]);
        $expectedKeys = [sprintf('0.%s', Digits::NOT_DIGITS)];

        // Execute
        $validator->isValid(['12a34']);

        // Assert
        $this->assertEquals($expectedKeys, array_keys($validator->getMessages()));
    }

    /**
     * @depends testIsValidSetsMessagesWithKeysEqualToTheIndexOfTheInvalidArrayItemAndTheOriginalValidatorKey
     */
    public function testIsValidSetsMessagesWithKeysEqualToTheIndexOfTheInvalidArrayItemAndTheOriginalValidatorKeyWhenSomeValuesAreValidAndOthersAreNot()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class]]]);
        $expectedKeys = [sprintf('1.%s', Digits::NOT_DIGITS)];

        // Execute
        $validator->isValid([1, 'a']);

        // Assert
        $this->assertEquals($expectedKeys, array_keys($validator->getMessages()));
    }

    /**
     * @depends testIsValidValidatesASingleValidValueAgainstAChildValidatorAndReturnsTrue
     */
    public function testIsValidValidatesAValidValueAgainstMultipleChildValidatorsAndReturnsTrue()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class], ['name' => NotEmpty::class]]]);

        // Execute
        $result = $validator->isValid([1]);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @depends testIsValidValidatesASingleValidValueAgainstAChildValidatorAndSetsNoMessages
     */
    public function testIsValidValidatesAValidValueAgainstMultipleChildValidatorsAndDoesNotSetMessages()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class], ['name' => NotEmpty::class]]]);

        // Execute
        $validator->isValid([1]);

        // Assert
        $this->assertEmpty($validator->getMessages());
    }

    /**
     * @depends testIsValidValidatesASingleInvalidValueAgainstAChildValidatorAndReturnsFalse
     */
    public function testIsValidValidatesAnInvalidValueAgainstMultipleChildValidatorsAndReturnsFalse()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class], ['name' => EmailAddress::class]]]);

        // Execute
        $result = $validator->isValid(['0']);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @depends testIsValidValidatesASingleInvalidValueAgainstAChildValidatorAndSetsMessages
     */
    public function testIsValidValidatesAnInvalidValueAgainstMultipleChildValidatorsAndSetsMessages()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class], ['name' => EmailAddress::class]]]);

        // Execute
        $validator->isValid(['0']);

        // Assert
        $this->assertCount(1, $validator->getMessages());
    }

    /**
     * @depends testIsValidValidatesAnInvalidValueAgainstMultipleChildValidatorsAndSetsMessages
     * @depends testIsValidSetsMessagesWithKeysEqualToTheIndexOfTheInvalidArrayItemAndTheOriginalValidatorKey
     */
    public function testIsValidSetsMessagesWithKeysEqualToTheIndexOfTheInvalidArrayItemAndTheOriginalValidatorKeyWithMultipleValidators()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class], ['name' => EmailAddress::class]]]);
        $expectedKeys = [
            sprintf('0.%s', EmailAddress::INVALID_FORMAT),
            sprintf('1.%s', Digits::NOT_DIGITS),
        ];

        // Execute
        $validator->isValid(['0', 'contact@dvsa.gov.uk']);

        // Assert
        $this->assertEquals($expectedKeys, array_keys($validator->getMessages()));
    }

    /**
     * @depends testIsValidator
     */
    public function testIsValidConfiguresTheOptionsForAChildValidator()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => GreaterThan::class, 'options' => ['min' => 0]]]]);

        // Execute
        $result = $validator->isValid([0]);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @depends testIsValidator
     */
    public function testGetMessagesReturnsNonUniqueMessagesWhenRepeatedForMultipleKeys()
    {
        // Setup
        $validator = new ValidateEach(['children' => [['name' => Digits::class]]]);

        // Execute
        $validator->isValid(['a', 'b']);

        // Assert
        $this->assertCount(2, $validator->getMessages());
    }
}
