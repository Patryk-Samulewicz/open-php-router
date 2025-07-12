<?php

declare(strict_types=1);

namespace OpenPhpRouter;

use OpenPhpRouter\DTO\ChatData;
use OpenPhpRouter\DTO\ModelData;
use OpenPhpRouter\DTO\GenerationCostData;
use OpenPhpRouter\DTO\ChatResponseData;
use OpenPhpRouter\Exception\OpenRouterException;

class OpenRouterClient
{
    private const API_BASE_URL = 'https://openrouter.ai/api/v1';
    
    private string $apiKey;
    private int $timeout;
    private string $referer;
    private string $title;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? throw new \InvalidArgumentException('API key is required');
        $this->timeout = $config['timeout'] ?? 30;
        $this->referer = $config['referer'] ?? 'https://github.com/open-php-router/open-php-router';
        $this->title = $config['title'] ?? 'open-php-router';
    }

    /**
     * Get list of available models with their pricing information
     *
     * @return ModelData[]
     * @throws OpenRouterException
     */
    public function listModels(): array
    {
        $response = $this->makeRequest('GET', '/models');
        $data = json_decode($response, true);
        
        if (!isset($data['data']) || !is_array($data['data'])) {
            throw new OpenRouterException('Invalid response format from OpenRouter API');
        }
        
        return array_map(
            fn(array $modelData) => ModelData::fromArray($modelData),
            $data['data']
        );
    }

    /**
     * Send a chat completion request
     *
     * @param ChatData $chatData
     * @return ChatResponseData
     * @throws OpenRouterException
     */
    public function chat(ChatData $chatData): ChatResponseData
    {
        $response = $this->makeRequest('POST', '/chat/completions', $chatData->toArray());
        $data = json_decode($response, true);
        
        return ChatResponseData::fromArray($data);
    }

    public function getGeneration(string $generationId, int $maxRetries = 5, int $retryDelay = 2): array
    {
        for ($i = 0; $i < $maxRetries; $i++) {
            $response = $this->makeRequest('GET', "/generation?id={$generationId}");
            $generationData = json_decode($response, true);

            if (!empty($generationData['data']['usage'])) {
                return $generationData;
            }

            if ($i < $maxRetries - 1) {
                sleep($retryDelay);
            }
        }

        return $generationData;
    }

    /**
     * Get costs for a specific request by generation ID
     *
     * @param string $generationId
     * @return GenerationCostData
     * @throws OpenRouterException
     */
    public function getRequestCosts(string $generationId, int $maxRetries = 5, int $retryDelay = 2): GenerationCostData
    {
        $generationData = $this->getGeneration($generationId, $maxRetries, $retryDelay);
        
        if (!isset($generationData['data']) || !is_array($generationData['data'])) {
            throw new OpenRouterException('Invalid generation data format from OpenRouter API');
        }
        
        return GenerationCostData::fromArray($generationData);
    }

    /**
     * Get costs for a chat request using the response data
     *
     * @param ChatResponseData $response
     * @return GenerationCostData
     * @throws OpenRouterException
     */
    public function getChatRequestCosts(ChatResponseData $response, int $maxRetries = 5, int $retryDelay = 2): GenerationCostData
    {
        return $this->getRequestCosts($response->getId(), $maxRetries, $retryDelay);
    }

    /**
     * Make HTTP request using cURL
     *
     * @param string $method
     * @param string $endpoint
     * @param array|null $data
     * @return string
     * @throws OpenRouterException
     */
    private function makeRequest(string $method, string $endpoint, ?array $data = null): string
    {
        $url = self::API_BASE_URL . $endpoint;
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: ' . $this->referer,
                'X-Title: ' . $this->title,
            ],
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new OpenRouterException('cURL error: ' . $error);
        }
        
        if ($httpCode < 200 || $httpCode >= 300) {
            $errorData = json_decode($response, true);
            
            $errorMessage = 'HTTP ' . $httpCode;
            
            if (is_array($errorData)) {
                if (isset($errorData['error']['message'])) {
                    $errorMessage = $errorData['error']['message'];
                } elseif (isset($errorData['message'])) {
                    $errorMessage = $errorData['message'];
                } elseif (isset($errorData['error'])) {
                    $errorMessage = is_string($errorData['error']) ? $errorData['error'] : json_encode($errorData['error']);
                }
                
                if (isset($errorData['error']['code'])) {
                    $errorMessage .= ' (Code: ' . $errorData['error']['code'] . ')';
                }
                if (isset($errorData['error']['type'])) {
                    $errorMessage .= ' (Type: ' . $errorData['error']['type'] . ')';
                }
            }
            
            if (strpos($errorMessage, 'Provider returned error') !== false) {
                $errorMessage .= ' It may be due to the model requiring special permissions or being in beta. Please check your OpenRouter account settings and model availability.';
            }
            
            throw new OpenRouterException('API error: ' . $errorMessage);
        }
        
        if ($response === false) {
            throw new OpenRouterException('Failed to get response from OpenRouter API');
        }
        
        return $response;
    }
} 