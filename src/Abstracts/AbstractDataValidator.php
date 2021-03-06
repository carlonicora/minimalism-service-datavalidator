<?php
namespace CarloNicora\Minimalism\Services\DataValidator\Abstracts;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\ObjectFactoryInterface;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Services\DataValidator\Factories\DataValidatorFactory;
use CarloNicora\Minimalism\Services\DataValidator\Interfaces\DataValidatorInterface;
use CarloNicora\Minimalism\Services\DataValidator\Objects\DocumentValidator;
use CarloNicora\Minimalism\Services\DataValidator\Objects\ValidationError;
use Exception;
use RuntimeException;

abstract class AbstractDataValidator implements DataValidatorInterface
{
    /** @var Document|null  */
    private ?Document $document=null;

    /** @var array|null  */
    protected ?array $existingData=null;

    /** @var SqlDataObjectInterface|null  */
    protected ?SqlDataObjectInterface $dataObject=null;

    /** @var Document|null  */
    protected ?Document $existingDocument=null;

    /** @var DocumentValidator */
    protected DocumentValidator $documentValidator;

    /**
     * @return Document
     */
    final public function getDocument(
    ): Document
    {
        return $this->document;
    }

    /**
     * @return ResourceObject
     */
    final public function getSingleResource(
    ): ResourceObject
    {
        if (!$this->documentValidator->isSingleResource){
            throw new RuntimeException('The document is expected to have multiple resources', 500);
        }

        return $this->document->getSingleResource();
    }

    /**
     * @return array|null
     */
    final public function getExistingData(
    ): ?array
    {
        return $this->existingData;
    }

    /**
     * @return Document|null
     */
    final public function getExistingDocument(): ?Document
    {
        return $this->existingDocument;
    }

    /**
     * @return SqlDataObjectInterface|null
     */
    final public function getDataObject(
    ): ?SqlDataObjectInterface
    {
        return $this->dataObject;
    }

    /**
     * @return ValidationError|null
     */
    public function getValidationError(
    ): ?ValidationError
    {
        return $this->documentValidator->getValidationError();
    }

    /**
     * @param array $payload
     * @throws Exception
     */
    final public function setDocument(
        array $payload,
    ): void
    {
        $this->document = new Document($payload);
    }

    /**
     * @return bool
     */
    final public function validate(
    ): bool
    {
        if ($this->document === null){
            return false;
        }

        return ($this->validateStructure() && $this->validateData());
    }

    /**
     * @return bool
     */
    final public function validateStructure(): bool
    {
        return $this->documentValidator->validate($this->document);
    }

    /**
     * @return bool
     */
    public function validateData(
    ): bool
    {
        return true;
    }
    
    /**
     * @return ObjectFactoryInterface|string
     */
    public function getObjectFactoryClass(
    ): DataValidatorFactory|string
    {
        return DataValidatorFactory::class;
    }
}