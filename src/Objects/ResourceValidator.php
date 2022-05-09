<?php
namespace CarloNicora\Minimalism\Services\DataValidator\Objects;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\DataValidator\Abstracts\AbstractValidator;
use CarloNicora\Minimalism\Services\DataValidator\Enums\ValidationErrors;
use CarloNicora\Minimalism\Services\DataValidator\Enums\ValidatorTypes;
use Exception;
use RuntimeException;

class ResourceValidator extends AbstractValidator
{
    /** @var AttributeValidator[]  */
    private array $attributesValidator=[];

    /**
     * @param string|null $type
     * @param array|null $acceptedRequiredTypes
     * @param bool $isIdRequired
     * @param bool $isSingleResource
     */
    public function __construct(
        private readonly ?string $type=null,
        private readonly ?array $acceptedRequiredTypes=null,
        private readonly bool $isIdRequired=false,
        private readonly bool $isSingleResource=true,
    )
    {
        if ($this->type === null && $this->acceptedRequiredTypes === null){
            throw new RuntimeException('At least one validation type between "type" and "acceptedRequiredTypes" must be set.', 500);
        }
    }

    /**
     * @param AttributeValidator $validator
     */
    public function addAttributeValidator(
        AttributeValidator $validator,
    ): void
    {
        $this->attributesValidator[] = $validator;
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
        if ($this->type !== null && $this->type !== $resource->type) {
            $this->setValidationError(
                new ValidationError(
                    error: ValidationErrors::typeMismatch,
                    description: '(expected: ' . $this->type . ' actual: ' . $resource->type . ')',
                    validatorType: ValidatorTypes::resource,
                )
            );
            return false;
        }

        if ($this->acceptedRequiredTypes !== null && !in_array($resource->type, $this->acceptedRequiredTypes, true)) {
            $acceptedTypes = implode(',', $this->acceptedRequiredTypes);
            $this->setValidationError(
                new ValidationError(
                    error: ValidationErrors::typeMismatch,
                    description: '(expected: ' . $acceptedTypes . ' actual: ' . $resource->type . ')',
                    validatorType: ValidatorTypes::resource,
                )
            );
            return false;
        }

        if ($this->isIdRequired && $resource->id === null){
            $this->setValidationError(
                new ValidationError(
                    error: ValidationErrors::idMissing,
                    description: '',
                    validatorType: ValidatorTypes::resource,
                )
            );

            return false;
        }

        if ($resource::class === Document::class && $this->isSingleResource && count($resource->resources) !== 1) {
            $this->setValidationError(
                new ValidationError(
                    error: ValidationErrors::numberOfResourcesMismatch,
                    description: '',
                    validatorType: ValidatorTypes::document,
                )
            );

            return false;
        }

        foreach ($this->attributesValidator ?? [] as $attributeValidator){
            if (!$attributeValidator->validate($resource)){
                $this->setValidationError($attributeValidator->getValidationError()??throw new RuntimeException('Missing Validation Error Definition', 500));
                return false;
            }
        }

        return true;
    }
}