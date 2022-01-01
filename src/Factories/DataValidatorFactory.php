<?php
namespace CarloNicora\Minimalism\Services\DataValidator\Factories;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Factories\MinimalismFactories;
use CarloNicora\Minimalism\Interfaces\ObjectFactoryInterface;
use CarloNicora\Minimalism\Interfaces\ObjectInterface;
use CarloNicora\Minimalism\Objects\ModelParameters;
use CarloNicora\Minimalism\Services\DataValidator\Interfaces\DataValidatorInterface;
use Exception;
use RuntimeException;

class DataValidatorFactory implements ObjectFactoryInterface
{
    /**
     * @param MinimalismFactories $minimalismFactories
     */
    public function __construct(
        private MinimalismFactories $minimalismFactories,
    )
    {
    }

    /**
     * @param string $className
     * @param string $parameterName
     * @param ModelParameters $parameters
     * @return ObjectInterface|null
     * @throws Exception
     */
    public function create(
        string $className,
        string $parameterName,
        ModelParameters $parameters,
    ): ?ObjectInterface
    {
        /** @var DataValidatorInterface $response */
        $response = $this->minimalismFactories->getObjectFactory()->createSimpleObject(
            className: $className,
            parameters: $parameters,
        );

        if ($parameters->getNamedParameter($parameterName) === null){
            throw new RuntimeException('Parameter ' . $parameterName . ' missing', HttpCode::PreconditionFailed->value);
        }

        $response->setDocument($parameters->getNamedParameter($parameterName));

        if (!$response->validate()) {
            throw new RuntimeException(
                message: $response->getValidationError()?->getValidatorType()->name
                . ' '
                . $response->getValidationError()?->getError()->name
                . ' '
                . $response->getValidationError()?->getDescription(),
                code: 412,
            );
        }

        return $response;
    }
}