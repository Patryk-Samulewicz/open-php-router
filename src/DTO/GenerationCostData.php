<?php

declare(strict_types=1);

namespace OpenPhpRouter\DTO;

class GenerationCostData
{
    public function __construct(
        private readonly string $id,
        private readonly string $model,
        private readonly int $promptTokens,
        private readonly int $completionTokens,
        private readonly float $promptCost,
        private readonly float $completionCost,
        private readonly float $totalCost,
        private readonly string $currency = 'USD',
        private readonly array $metadata = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        // Obsługa zagnieżdżenia w ['data'] (format OpenRouter)
        if (isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }

        $tokensPrompt = $data['tokens_prompt'] ?? 0;
        $tokensCompletion = $data['tokens_completion'] ?? 0;
        
        // Pobierz total_cost z usage lub total_cost
        $totalCost = (float) ($data['usage'] ?? $data['total_cost'] ?? 0.0);

        // Rozdziel koszt na prompt/completion proporcjonalnie do liczby tokenów
        $promptCost = 0.0;
        $completionCost = 0.0;
        $totalTokens = $tokensPrompt + $tokensCompletion;
        if ($totalTokens > 0) {
            $promptCost = $totalCost * ($tokensPrompt / $totalTokens);
            $completionCost = $totalCost * ($tokensCompletion / $totalTokens);
        }

        return new self(
            id: $data['id'] ?? '',
            model: $data['model'] ?? '',
            promptTokens: $tokensPrompt,
            completionTokens: $tokensCompletion,
            promptCost: $promptCost,
            completionCost: $completionCost,
            totalCost: $totalCost,
            currency: 'USD',
            metadata: $data
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getPromptTokens(): int
    {
        return $this->promptTokens;
    }

    public function getCompletionTokens(): int
    {
        return $this->completionTokens;
    }

    public function getTotalTokens(): int
    {
        return $this->promptTokens + $this->completionTokens;
    }

    public function getPromptCost(): float
    {
        return $this->promptCost;
    }

    public function getCompletionCost(): float
    {
        return $this->completionCost;
    }

    public function getTotalCost(): float
    {
        return $this->totalCost;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get cost per 1K tokens for prompt (input)
     */
    public function getPromptCostPer1K(): float
    {
        if ($this->promptTokens === 0) {
            return 0.0;
        }
        
        return ($this->promptCost / $this->promptTokens) * 1000;
    }

    /**
     * Get cost per 1K tokens for completion (output)
     */
    public function getCompletionCostPer1K(): float
    {
        if ($this->completionTokens === 0) {
            return 0.0;
        }
        
        return ($this->completionCost / $this->completionTokens) * 1000;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'prompt_tokens' => $this->promptTokens,
            'completion_tokens' => $this->completionTokens,
            'total_tokens' => $this->getTotalTokens(),
            'prompt_cost' => $this->promptCost,
            'completion_cost' => $this->completionCost,
            'total_cost' => $this->totalCost,
            'currency' => $this->currency,
            'prompt_cost_per_1k' => $this->getPromptCostPer1K(),
            'completion_cost_per_1k' => $this->getCompletionCostPer1K(),
            'metadata' => $this->metadata,
        ];
    }
} 