<?php

declare(strict_types=1);

namespace OpenPhpRouter\DTO;

class ChatResponseData
{
    public function __construct(
        private readonly string $id,
        private readonly string $model,
        private readonly array $choices,
        private readonly int $created,
        private readonly string $object,
        private readonly ?array $usage = null,
        private readonly ?string $systemFingerprint = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            model: $data['model'] ?? '',
            choices: $data['choices'] ?? [],
            created: $data['created'] ?? 0,
            object: $data['object'] ?? '',
            usage: $data['usage'] ?? null,
            systemFingerprint: $data['system_fingerprint'] ?? null
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

    public function getChoices(): array
    {
        return $this->choices;
    }

    public function getCreated(): int
    {
        return $this->created;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function getUsage(): ?array
    {
        return $this->usage;
    }

    public function getSystemFingerprint(): ?string
    {
        return $this->systemFingerprint;
    }

    /**
     * Get the content from the first choice (for non-streaming responses)
     */
    public function getContent(): ?string
    {
        if (empty($this->choices)) {
            return null;
        }
        
        $firstChoice = $this->choices[0];
        return $firstChoice['message']['content'] ?? null;
    }

    /**
     * Get the role from the first choice
     */
    public function getRole(): ?string
    {
        if (empty($this->choices)) {
            return null;
        }
        
        $firstChoice = $this->choices[0];
        return $firstChoice['message']['role'] ?? null;
    }

    /**
     * Get finish reason from the first choice
     */
    public function getFinishReason(): ?string
    {
        if (empty($this->choices)) {
            return null;
        }
        
        $firstChoice = $this->choices[0];
        return $firstChoice['finish_reason'] ?? null;
    }

    /**
     * Get prompt tokens from usage data
     */
    public function getPromptTokens(): ?int
    {
        return $this->usage['prompt_tokens'] ?? null;
    }

    /**
     * Get completion tokens from usage data
     */
    public function getCompletionTokens(): ?int
    {
        return $this->usage['completion_tokens'] ?? null;
    }

    /**
     * Get total tokens from usage data
     */
    public function getTotalTokens(): ?int
    {
        return $this->usage['total_tokens'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'model' => $this->model,
            'choices' => $this->choices,
            'created' => $this->created,
            'object' => $this->object,
            'usage' => $this->usage,
            'system_fingerprint' => $this->systemFingerprint,
        ];
    }
} 