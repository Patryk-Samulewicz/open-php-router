<?php

declare(strict_types=1);

namespace OpenPhpRouter\Tests;

use OpenPhpRouter\OpenRouterClient;
use OpenPhpRouter\DTO\ChatData;
use OpenPhpRouter\DTO\MessageData;
use OpenPhpRouter\DTO\ModelData;
use OpenPhpRouter\DTO\GenerationCostData;
use OpenPhpRouter\Enum\RoleType;
use OpenPhpRouter\Exception\OpenRouterException;
use PHPUnit\Framework\TestCase;
use OpenPhpRouter\DTO\ChatResponseData;

class OpenRouterClientTest extends TestCase
{
    private OpenRouterClient $client;

    protected function setUp(): void
    {
        $this->client = new OpenRouterClient([
            'api_key' => 'test_api_key',
        ]);
    }

    public function testClientInitialization(): void
    {
        $this->assertInstanceOf(OpenRouterClient::class, $this->client);
    }

    public function testClientInitializationWithoutApiKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('API key is required');
        
        new OpenRouterClient([]);
    }

    public function testClientInitializationWithCustomConfig(): void
    {
        $client = new OpenRouterClient([
            'api_key' => 'test_key',
            'timeout' => 60,
            'referer' => 'https://test.com',
            'title' => 'test-app',
        ]);
        
        $this->assertInstanceOf(OpenRouterClient::class, $client);
    }

    public function testChatDataCreation(): void
    {
        $chatData = new ChatData(
            messages: [
                MessageData::createUserMessage('Hello'),
            ],
            model: 'test-model',
            maxTokens: 100,
        );

        $this->assertInstanceOf(ChatData::class, $chatData);
        $this->assertEquals('test-model', $chatData->getModel());
        $this->assertEquals(100, $chatData->getMaxTokens());
    }

    public function testMessageDataCreation(): void
    {
        $message = MessageData::createUserMessage('Hello, world!');
        
        $this->assertInstanceOf(MessageData::class, $message);
        $this->assertEquals(RoleType::USER, $message->getRole());
        $this->assertEquals('Hello, world!', $message->getContent());
    }

    public function testMessageDataCreationWithName(): void
    {
        $message = MessageData::createUserMessage('Hello, world!', 'test-user');
        
        $this->assertInstanceOf(MessageData::class, $message);
        $this->assertEquals(RoleType::USER, $message->getRole());
        $this->assertEquals('Hello, world!', $message->getContent());
        $this->assertEquals('test-user', $message->getName());
    }

    public function testSystemMessageCreation(): void
    {
        $message = MessageData::createSystemMessage('You are a helpful assistant.');
        
        $this->assertInstanceOf(MessageData::class, $message);
        $this->assertEquals(RoleType::SYSTEM, $message->getRole());
        $this->assertEquals('You are a helpful assistant.', $message->getContent());
    }

    public function testAssistantMessageCreation(): void
    {
        $message = MessageData::createAssistantMessage('I can help you with that.');
        
        $this->assertInstanceOf(MessageData::class, $message);
        $this->assertEquals(RoleType::ASSISTANT, $message->getRole());
        $this->assertEquals('I can help you with that.', $message->getContent());
    }

    public function testToolMessageCreation(): void
    {
        $message = MessageData::createToolMessage('Tool result', 'call_123');
        
        $this->assertInstanceOf(MessageData::class, $message);
        $this->assertEquals(RoleType::TOOL, $message->getRole());
        $this->assertEquals('Tool result', $message->getContent());
        $this->assertEquals('call_123', $message->getToolCallId());
    }

    public function testModelDataCreation(): void
    {
        $modelData = ModelData::fromArray([
            'id' => 'test-model',
            'name' => 'Test Model',
            'description' => 'A test model',
            'provider' => 'test-provider',
            'pricing' => [
                'prompt' => 0.001,
                'completion' => 0.002,
                'unit' => 'USD',
            ],
            'context_length' => 4096,
        ]);

        $this->assertInstanceOf(ModelData::class, $modelData);
        $this->assertEquals('test-model', $modelData->getId());
        $this->assertEquals('Test Model', $modelData->getName());
        $this->assertEquals(1.0, $modelData->getPromptPricePer1K());
        $this->assertEquals(2.0, $modelData->getCompletionPricePer1K());
    }

    public function testGenerationCostDataCreation(): void
    {
        $costData = GenerationCostData::fromArray([
            'id' => 'gen-test123',
            'model' => 'test-model',
            'tokens_prompt' => 10,
            'tokens_completion' => 20,
            'usage' => 0.003,
        ]);

        $this->assertInstanceOf(GenerationCostData::class, $costData);
        $this->assertEquals('gen-test123', $costData->getId());
        $this->assertEquals(10, $costData->getPromptTokens());
        $this->assertEquals(20, $costData->getCompletionTokens());
        $this->assertEquals(0.003, $costData->getTotalCost());
        $this->assertEquals(30, $costData->getTotalTokens());
    }

    public function testChatDataToArray(): void
    {
        $chatData = new ChatData(
            messages: [
                MessageData::createUserMessage('Hello'),
            ],
            model: 'test-model',
            maxTokens: 100,
            temperature: 0.7,
        );

        $array = $chatData->toArray();
        
        $this->assertArrayHasKey('messages', $array);
        $this->assertArrayHasKey('model', $array);
        $this->assertArrayHasKey('max_tokens', $array);
        $this->assertArrayHasKey('temperature', $array);
        $this->assertEquals('test-model', $array['model']);
        $this->assertEquals(100, $array['max_tokens']);
        $this->assertEquals(0.7, $array['temperature']);
    }

    public function testMessageDataToArray(): void
    {
        $message = MessageData::createUserMessage('Hello, world!', 'test-user');
        
        $array = $message->toArray();
        
        $this->assertArrayHasKey('role', $array);
        $this->assertArrayHasKey('content', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertEquals('user', $array['role']);
        $this->assertEquals('Hello, world!', $array['content']);
        $this->assertEquals('test-user', $array['name']);
    }

    public function testMessageDataToArrayWithoutName(): void
    {
        $message = MessageData::createUserMessage('Hello, world!');
        
        $array = $message->toArray();
        
        $this->assertArrayHasKey('role', $array);
        $this->assertArrayHasKey('content', $array);
        $this->assertArrayNotHasKey('name', $array);
        $this->assertEquals('user', $array['role']);
        $this->assertEquals('Hello, world!', $array['content']);
    }

    public function testToolMessageDataToArray(): void
    {
        $message = MessageData::createToolMessage('Tool result', 'call_123');
        
        $array = $message->toArray();
        
        $this->assertArrayHasKey('role', $array);
        $this->assertArrayHasKey('content', $array);
        $this->assertArrayHasKey('tool_call_id', $array);
        $this->assertEquals('tool', $array['role']);
        $this->assertEquals('Tool result', $array['content']);
        $this->assertEquals('call_123', $array['tool_call_id']);
    }

    public function testGetRequestCosts(): void
    {
        // Mock response data for generation
        $mockGenerationData = [
            'data' => [
                'id' => 'gen-test123',
                'model' => 'test-model',
                'tokens_prompt' => 10,
                'tokens_completion' => 20,
                'usage' => 0.003,
                'total_cost' => 0.003,
            ]
        ];

        // Create a mock client that returns our test data
        $client = $this->getMockBuilder(OpenRouterClient::class)
            ->setConstructorArgs([['api_key' => 'test_key']])
            ->onlyMethods(['getGeneration'])
            ->getMock();

        $client->expects($this->once())
            ->method('getGeneration')
            ->with('gen-test123')
            ->willReturn($mockGenerationData);

        $costs = $client->getRequestCosts('gen-test123');

        $this->assertInstanceOf(GenerationCostData::class, $costs);
        $this->assertEquals('gen-test123', $costs->getId());
        $this->assertEquals('test-model', $costs->getModel());
        $this->assertEquals(10, $costs->getPromptTokens());
        $this->assertEquals(20, $costs->getCompletionTokens());
        $this->assertEquals(0.003, $costs->getTotalCost());
    }

    public function testGetChatRequestCosts(): void
    {
        // Mock response data for generation
        $mockGenerationData = [
            'data' => [
                'id' => 'gen-test456',
                'model' => 'test-model',
                'tokens_prompt' => 15,
                'tokens_completion' => 25,
                'usage' => 0.005,
                'total_cost' => 0.005,
            ]
        ];

        // Create a mock client that returns our test data
        $client = $this->getMockBuilder(OpenRouterClient::class)
            ->setConstructorArgs([['api_key' => 'test_key']])
            ->onlyMethods(['getGeneration'])
            ->getMock();

        $client->expects($this->once())
            ->method('getGeneration')
            ->with('gen-test456')
            ->willReturn($mockGenerationData);

        // Create a mock ChatResponseData
        $mockResponse = $this->createMock(ChatResponseData::class);
        $mockResponse->method('getId')->willReturn('gen-test456');

        $costs = $client->getChatRequestCosts($mockResponse);

        $this->assertInstanceOf(GenerationCostData::class, $costs);
        $this->assertEquals('gen-test456', $costs->getId());
        $this->assertEquals('test-model', $costs->getModel());
        $this->assertEquals(15, $costs->getPromptTokens());
        $this->assertEquals(25, $costs->getCompletionTokens());
        $this->assertEquals(0.005, $costs->getTotalCost());
    }

    public function testGetRequestCostsWithInvalidData(): void
    {
        $client = $this->getMockBuilder(OpenRouterClient::class)
            ->setConstructorArgs([['api_key' => 'test_key']])
            ->onlyMethods(['getGeneration'])
            ->getMock();

        $client->expects($this->once())
            ->method('getGeneration')
            ->with('invalid-id')
            ->willReturn(['invalid' => 'data']);

        $this->expectException(OpenRouterException::class);
        $this->expectExceptionMessage('Invalid generation data format from OpenRouter API');

        $client->getRequestCosts('invalid-id');
    }
} 