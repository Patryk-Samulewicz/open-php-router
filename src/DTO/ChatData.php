<?php

declare(strict_types=1);

namespace OpenPhpRouter\DTO;

class ChatData
{
    /**
     * @see https://openrouter.ai/docs/api-reference/chat-completion
     *
     * @param array $messages
     * @param string|null $model
     * @param int|null $maxTokens
     * @param float|null $temperature
     * @param float|null $topP
     * @param bool|null $stream
     * @param array|string|null $stop
     * @param array|null $responseFormat
     * @param array|null $usage
     * @param array|null $tools
     * @param string|null $toolChoice
     * @param array|null $logitBias
     * @param int|null $seed
     * @param array|null $transforms
     * @param array|null $models
     * @param string|null $route
     * @param array|null $provider
     * @param string|null $user
     * @param float|null $frequencyPenalty
     * @param float|null $presencePenalty
     * @param float|null $repetitionPenalty
     * @param float|null $minP
     * @param float|null $topA
     * @param array|null $reasoning
     */
    public function __construct(
        private readonly array $messages = [],
        private readonly ?string $model = null,
        private readonly ?int $maxTokens = null,
        private readonly ?float $temperature = null,
        private readonly ?float $topP = null,
        private readonly ?bool $stream = null,
        private readonly array|string|null $stop = null,
        private readonly ?array $responseFormat = null,
        private readonly ?array $usage = null,
        private readonly ?array $tools = null,
        private readonly mixed $toolChoice = null,
        private readonly ?array $logitBias = null,
        private readonly ?int $seed = null,
        private readonly ?array $transforms = null,
        private readonly ?array $models = null,
        private readonly ?string $route = null,
        private readonly ?array $provider = null,
        private readonly ?string $user = null,
        private readonly ?float $frequencyPenalty = null,
        private readonly ?float $presencePenalty = null,
        private readonly ?float $repetitionPenalty = null,
        private readonly ?float $minP = null,
        private readonly ?float $topA = null,
        private readonly ?array $reasoning = null
    ) {
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function getMaxTokens(): ?int
    {
        return $this->maxTokens;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function getTopP(): ?float
    {
        return $this->topP;
    }

    public function getStream(): ?bool
    {
        return $this->stream;
    }

    public function getStop(): array|string|null
    {
        return $this->stop;
    }

    public function getResponseFormat(): ?array
    {
        return $this->responseFormat;
    }

    public function getUsage(): ?array
    {
        return $this->usage;
    }

    public function getTools(): ?array
    {
        return $this->tools;
    }

    public function getToolChoice(): mixed
    {
        return $this->toolChoice;
    }

    public function getLogitBias(): ?array
    {
        return $this->logitBias;
    }

    public function getSeed(): ?int
    {
        return $this->seed;
    }

    public function getTransforms(): ?array
    {
        return $this->transforms;
    }

    public function getModels(): ?array
    {
        return $this->models;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function getProvider(): ?array
    {
        return $this->provider;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getFrequencyPenalty(): ?float
    {
        return $this->frequencyPenalty;
    }

    public function getPresencePenalty(): ?float
    {
        return $this->presencePenalty;
    }

    public function getRepetitionPenalty(): ?float
    {
        return $this->repetitionPenalty;
    }

    public function getMinP(): ?float
    {
        return $this->minP;
    }

    public function getTopA(): ?float
    {
        return $this->topA;
    }

    public function getReasoning(): ?array
    {
        return $this->reasoning;
    }

    public function toArray(): array
    {
        $data = [];
        
        if (!empty($this->messages)) {
            $data['messages'] = array_map(function ($message) {
                if ($message instanceof MessageData) {
                    return $message->toArray();
                }
                return $message;
            }, $this->messages);
        }
        
        if ($this->model !== null) {
            $data['model'] = $this->model;
        }
        
        if ($this->maxTokens !== null) {
            $data['max_tokens'] = $this->maxTokens;
        }
        
        if ($this->temperature !== null) {
            $data['temperature'] = $this->temperature;
        }
        
        if ($this->topP !== null) {
            $data['top_p'] = $this->topP;
        }
        
        if ($this->stream !== null) {
            $data['stream'] = $this->stream;
        }
        
        if ($this->stop !== null) {
            $data['stop'] = $this->stop;
        }
        
        if ($this->responseFormat !== null) {
            $data['response_format'] = $this->responseFormat;
        }
        
        if ($this->usage !== null) {
            $data['usage'] = $this->usage;
        }
        
        if ($this->tools !== null) {
            $data['tools'] = $this->tools;
        }
        
        if ($this->toolChoice !== null) {
            $data['tool_choice'] = $this->toolChoice;
        }
        
        if ($this->logitBias !== null) {
            $data['logit_bias'] = $this->logitBias;
        }
        
        if ($this->seed !== null) {
            $data['seed'] = $this->seed;
        }
        
        if ($this->transforms !== null) {
            $data['transforms'] = $this->transforms;
        }
        
        if ($this->models !== null) {
            $data['models'] = $this->models;
        }
        
        if ($this->route !== null) {
            $data['route'] = $this->route;
        }
        
        if ($this->provider !== null) {
            $data['provider'] = $this->provider;
        }
        
        if ($this->user !== null) {
            $data['user'] = $this->user;
        }

        if ($this->frequencyPenalty !== null) {
            $data['frequency_penalty'] = $this->frequencyPenalty;
        }

        if ($this->presencePenalty !== null) {
            $data['presence_penalty'] = $this->presencePenalty;
        }

        if ($this->repetitionPenalty !== null) {
            $data['repetition_penalty'] = $this->repetitionPenalty;
        }

        if ($this->minP !== null) {
            $data['min_p'] = $this->minP;
        }

        if ($this->topA !== null) {
            $data['top_a'] = $this->topA;
        }
        
        return $data;
    }
} 