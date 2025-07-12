<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OpenPhpRouter\DTO\GenerationCostData;
use OpenPhpRouter\OpenRouterClient;
use OpenPhpRouter\DTO\ChatData;
use OpenPhpRouter\DTO\MessageData;
use OpenPhpRouter\Enum\RoleType;


if ($apiKey === 'your_api_key_here') {
    echo "Ustaw zmienną środowiskową OPENROUTER_API_KEY lub edytuj ten plik.\n";
    exit(1);
}

try {
    // Inicjalizacja klienta z natywnym cURL
    $client = new OpenRouterClient([
        'api_key' => $apiKey,
        'timeout' => 30,
        'referer' => 'https://github.com/open-php-router/open-php-router',
        'title' => 'open-php-router-example',
    ]);

    echo "=== Pobieranie listy modeli ===\n";
    $models = $client->listModels();

    //Sortowanie od najdroższych modeli
    usort($models, function ($a, $b) {
        $priceA = $a->getPromptPricePer1K() + $a->getCompletionPricePer1K();
        $priceB = $b->getPromptPricePer1K() + $b->getCompletionPricePer1K();
        return $priceB <=> $priceA; // Sortowanie malejąco
    });
    
    // Wyświetl pierwsze 5 modeli z cenami
    $count = 0;
    foreach ($models as $model) {
        if ($count >= 5) break;
        
        echo sprintf(
            "%s (%s) - Prompt: $%.4f/1K, Completion: $%.4f/1K\n",
            $model->getName(),
            $model->getProvider(),
            $model->getPromptPricePer1K() ?? 0,
            $model->getCompletionPricePer1K() ?? 0
        );
        $count++;
    }

    echo "\n=== Szczegóły modelu ===\n";
    echo sprintf(
        "Model: %s\nDescription: %s\nContext length: %d\nPrompt price per 1K: $%.4f\nCompletion price per 1K: $%.4f\n",
        $models[17]->getName(),
        $models[17]->getDescription(),
        $models[17]->getContextLength() ?? 0,
        $models[17]->getPromptPricePer1K() ?? 0,
        $models[17]->getCompletionPricePer1K() ?? 0,
    );

    echo "\n=== Wysyłanie zapytania chat ===\n";
    
    // Utwórz zapytanie chat
    $chatData = new ChatData(
        messages: [
            MessageData::createUserMessage('Hello, who are you?'),
        ],
        model: $models[17]->getId(), // Wybierz model z listy
        maxTokens: 100,
        temperature: 0.7,
        usage: ['include' => true],
    );

    try {
        $response = $client->chat($chatData);
        
        echo "Odpowiedź: " . $response->getContent() . "\n";
        echo "Model: " . $response->getModel() . "\n";
        echo "ID generowania: " . $response->getId() . "\n";
        
        if ($response->getUsage()) {
            $usage = $response->getUsage();
            echo "\n=== Koszty z usage w odpowiedzi ===\n";
            if (isset($usage['prompt_tokens'])) {
                echo "Prompt tokens: {$usage['prompt_tokens']}\n";
            }
            if (isset($usage['completion_tokens'])) {
                echo "Completion tokens: {$usage['completion_tokens']}\n";
            }
            if (isset($usage['total_tokens'])) {
                echo "Total tokens: {$usage['total_tokens']}\n";
            }
        }
    } catch (OpenPhpRouter\Exception\OpenRouterException $e) {
        echo "Błąd podczas wysyłania zapytania do modelu: " . $chatData->getModel() . "\n";
        echo "Szczegóły błędu: " . $e->getMessage() . "\n";
        
        // Dodaj sugestie dla typowych błędów
        if (strpos($e->getMessage(), 'Provider returned error') !== false) {
            echo "\nMożliwe przyczyny:\n";
            echo "- Model wymaga specjalnych uprawnień lub subskrypcji\n";
            echo "- Model jest w trybie beta i nie jest dostępny dla Twojego konta\n";
            echo "- Brak dostępu do providera (OpenAI, Anthropic, etc.)\n";
            echo "- Model może być tymczasowo niedostępny\n";
        }
        
        exit(1);
    }

    echo "\n=== Sprawdzanie kosztów generowania ===\n";
    
    // Sprawdzanie kosztów generowania z ponownymi próbami
    $generationId = $response->getId();
    $maxRetries = 5;
    $retryDelay = 2; // sekundy

    for ($i = 0; $i < $maxRetries; $i++) {
        try {
            $generationData = $client->getGeneration($generationId);
            
            if (!empty($generationData['data']['usage'])) {
                $cost = GenerationCostData::fromArray($generationData);
                
                echo sprintf(
                    "Prompt tokens: %d (%.4f USD)\n",
                    $cost->getPromptTokens(),
                    $cost->getPromptCost()
                );
                
                echo sprintf(
                    "Completion tokens: %d (%.4f USD)\n",
                    $cost->getCompletionTokens(),
                    $cost->getCompletionCost()
                );
                
                echo sprintf(
                    "Total cost: %.4f USD\n",
                    $cost->getTotalCost()
                );
                
                echo sprintf(
                    "Cost per 1K prompt tokens: %.4f USD\n",
                    $cost->getPromptCostPer1K()
                );
                
                echo sprintf(
                    "Cost per 1K completion tokens: %.4f USD\n",
                    $cost->getCompletionCostPer1K()
                );
                
                break; // Wyjdź z pętli, jeśli dane są dostępne
            }
        } catch (\OpenPhpRouter\Exception\OpenRouterException $e) {
            echo "Błąd podczas pobierania danych generowania: " . $e->getMessage() . "\n";
        }

        if ($i < $maxRetries - 1) {
            echo "Dane o kosztach nie są jeszcze dostępne. Próba ponowna za {$retryDelay}s...\n";
            sleep($retryDelay);
        } else {
            echo "Nie udało się pobrać szczegółowych kosztów po {$maxRetries} próbach.\n";
        }
    }

    echo "\n=== Użycie nowych metod do pobierania kosztów ===\n";
    
    // Przykład użycia nowej metody getRequestCosts()
    try {
        $costs = $client->getRequestCosts($generationId);
        
        echo "=== Koszty pobrane przez getRequestCosts() ===\n";
        echo sprintf("ID generowania: %s\n", $costs->getId());
        echo sprintf("Model: %s\n", $costs->getModel());
        echo sprintf("Prompt tokens: %d\n", $costs->getPromptTokens());
        echo sprintf("Completion tokens: %d\n", $costs->getCompletionTokens());
        echo sprintf("Total tokens: %d\n", $costs->getTotalTokens());
        echo sprintf("Prompt cost: %.4f USD\n", $costs->getPromptCost());
        echo sprintf("Completion cost: %.4f USD\n", $costs->getCompletionCost());
        echo sprintf("Total cost: %.4f USD\n", $costs->getTotalCost());
        echo sprintf("Cost per 1K prompt tokens: %.4f USD\n", $costs->getPromptCostPer1K());
        echo sprintf("Cost per 1K completion tokens: %.4f USD\n", $costs->getCompletionCostPer1K());
        
    } catch (\OpenPhpRouter\Exception\OpenRouterException $e) {
        echo "Błąd podczas pobierania kosztów przez getRequestCosts(): " . $e->getMessage() . "\n";
    }

    // Przykład użycia nowej metody getChatRequestCosts()
    try {
        $costs = $client->getChatRequestCosts($response);
        
        echo "\n=== Koszty pobrane przez getChatRequestCosts() ===\n";
        echo sprintf("ID generowania: %s\n", $costs->getId());
        echo sprintf("Model: %s\n", $costs->getModel());
        echo sprintf("Total cost: %.4f USD\n", $costs->getTotalCost());
        echo sprintf("Currency: %s\n", $costs->getCurrency());
        
    } catch (\OpenPhpRouter\Exception\OpenRouterException $e) {
        echo "Błąd podczas pobierania kosztów przez getChatRequestCosts(): " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "Błąd: " . $e->getMessage() . "\n";
    exit(1);
}