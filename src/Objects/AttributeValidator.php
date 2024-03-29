<?php
namespace CarloNicora\Minimalism\Services\DataValidator\Objects;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\DataValidator\Abstracts\AbstractValidator;
use CarloNicora\Minimalism\Services\DataValidator\Enums\DataTypes;
use CarloNicora\Minimalism\Services\DataValidator\Enums\ValidationErrors;
use CarloNicora\Minimalism\Services\DataValidator\Enums\ValidatorTypes;
use Exception;

class AttributeValidator extends AbstractValidator
{
    /**
     * @param string $name
     * @param bool $isRequired
     * @param DataTypes $type
     */
    public function __construct(
        string $name,
        private readonly bool $isRequired=false,
        private readonly DataTypes $type=DataTypes::string,
    )
    {
        $this->name = $name;
    }

    /**
     * @param Document|ResourceObject $resource
     * @return bool
     * @throws Exception
     */
    public function validate(
        Document|ResourceObject $resource,
    ): bool
    {
        if (! $resource->attributes->has($this->name)) {
            if (! $this->isRequired) {
                return true;
            }

            $this->setValidationError(
                new ValidationError(
                    error: ValidationErrors::attributeMissing,
                    description: $this->name,
                    validatorType: ValidatorTypes::attribute,
                )
            );
            return false;
        }

        $attributeValue = $resource->attributes->get($this->name);
        if ($attributeValue === null) {
            return ! $this->isRequired;
        }

        $type = DataTypes::tryFrom(gettype($attributeValue));
        if ($this->type !== $type && !($type === DataTypes::int && $this->type === DataTypes::float)) {
            $this->setValidationError(
                new ValidationError(
                    error: ValidationErrors::typeMismatch,
                    description: $this->name . ' (expected: ' . $type?->value . ' actual: ' . $this->type->value . ')',
                    validatorType: ValidatorTypes::attribute,
                )
            );
            return false;
        }

        return true;
    }
}