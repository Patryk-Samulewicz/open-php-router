<?php

declare(strict_types=1);

namespace OpenPhpRouter\DTO;

use OpenPhpRouter\Enum\RoleType;

class MessageData
{
    public function __construct(
        private readonly RoleType $role,
        private readonly string|array $content,
        private readonly ?string $name = null,
        private readonly ?string $toolCallId = null
    ) {
    }

    public function getRole(): RoleType
    {
        return $this->role;
    }

    public function getContent(): string|array
    {
        return $this->content;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getToolCallId(): ?string
    {
        return $this->toolCallId;
    }

    public function toArray(): array
    {
        $data = [
            'role' => $this->role->value,
            'content' => $this->content,
        ];
        
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        
        if ($this->toolCallId !== null) {
            $data['tool_call_id'] = $this->toolCallId;
        }
        
        return $data;
    }

    /**
     * Create a simple text message
     */
    public static function createTextMessage(RoleType $role, string $content, ?string $name = null): self
    {
        return new self($role, $content, $name);
    }

    /**
     * Create a system message
     */
    public static function createSystemMessage(string $content): self
    {
        return new self(RoleType::SYSTEM, $content);
    }

    /**
     * Create a user message
     */
    public static function createUserMessage(string $content, ?string $name = null): self
    {
        return new self(RoleType::USER, $content, $name);
    }

    /**
     * Create an assistant message
     */
    public static function createAssistantMessage(string $content, ?string $name = null): self
    {
        return new self(RoleType::ASSISTANT, $content, $name);
    }

    /**
     * Create a tool message
     */
    public static function createToolMessage(string $content, string $toolCallId, ?string $name = null): self
    {
        return new self(RoleType::TOOL, $content, $name, $toolCallId);
    }
} 