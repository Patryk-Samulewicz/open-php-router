<?php

declare(strict_types=1);

namespace OpenPhpRouter\DTO;

class ModelData
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $description,
        private readonly string $provider,
        private readonly ?float $promptPrice = null,
        private readonly ?float $completionPrice = null,
        private readonly ?int $contextLength = null,
        private readonly ?string $pricingUnit = null,
        private readonly array $supportedParameters = [],
        private readonly array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        $promptPrice = isset($data['pricing']['prompt']) && $data['pricing']['prompt'] !== ''
            ? (float)$data['pricing']['prompt']
            : null;
        $completionPrice = isset($data['pricing']['completion']) && $data['pricing']['completion'] !== ''
            ? (float)$data['pricing']['completion']
            : null;
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            description: $data['description'] ?? '',
            provider: $data['provider'] ?? 'unknown',
            promptPrice: $promptPrice,
            completionPrice: $completionPrice,
            contextLength: $data['context_length'] ?? null,
            pricingUnit: $data['pricing']['unit'] ?? null,
            supportedParameters: $data['supported_parameters'] ?? [],
            metadata: $data['metadata'] ?? []
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getPromptPrice(): ?float
    {
        return $this->promptPrice;
    }

    public function getCompletionPrice(): ?float
    {
        return $this->completionPrice;
    }

    public function getContextLength(): ?int
    {
        return $this->contextLength;
    }

    public function getPricingUnit(): ?string
    {
        return $this->pricingUnit;
    }

    public function getSupportedParameters(): array
    {
        return $this->supportedParameters;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get price per 1K tokens for prompt (input)
     */
    public function getPromptPricePer1K(): ?float
    {
        return $this->promptPrice !== null ? $this->promptPrice * 1000 : null;
    }

    /**
     * Get price per 1K tokens for completion (output)
     */
    public function getCompletionPricePer1K(): ?float
    {
        return $this->completionPrice !== null ? $this->completionPrice * 1000 : null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'provider' => $this->provider,
            'prompt_price' => $this->promptPrice,
            'completion_price' => $this->completionPrice,
            'context_length' => $this->contextLength,
            'pricing_unit' => $this->pricingUnit,
            'supported_parameters' => $this->supportedParameters,
            'metadata' => $this->metadata,
        ];
    }
} 