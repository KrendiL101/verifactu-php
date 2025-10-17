<?php

declare(strict_types=1);

namespace eseperio\verifactu\tests\Unit\Models;

use eseperio\verifactu\models\Model;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    /**
     * Test for model validation.
     */
    public function testValidation(): void
    {
        // Use a concrete implementation of the abstract Model class
        $model = new class () extends Model {
            public $requiredField;

            public $optionalField;

            public function rules(): array
            {
                return [
                    [['requiredField'], 'required'],
                    [['optionalField'], fn($value): bool|string => is_null($value) || is_string($value) ? true : 'Must be string or null.'],
                ];
            }
        };

        // Test validation fails for missing required field
        $errors = $model->validate();
        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        // Error keys are namespaced (Class::$property); assert any key contains 'requiredField'
        $this->assertTrue((bool) array_filter(array_keys($errors), fn($k) => str_contains($k, 'requiredField')), 'Errors should contain requiredField');

        // Test validation passes when required fields are set
        $model->requiredField = 'test';
        $errors = $model->validate();
        $this->assertIsArray($errors);
        $this->assertEmpty($errors);

        // Test validation with custom validator function
        $model->optionalField = 123; // Not a string
        $errors = $model->validate();
        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        // Namespaced keys; ensure optionalField appears
        $this->assertTrue((bool) array_filter(array_keys($errors), fn($k) => str_contains($k, 'optionalField')), 'Errors should contain optionalField');

        // Fix the field and test again
        $model->optionalField = 'valid string';
        $errors = $model->validate();
        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }
}
