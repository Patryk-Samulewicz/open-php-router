# Open PHP Router

Nowoczesna paczka PHP do integracji z OpenRouter API. Umożliwia łatwe pobieranie listy modeli, wysyłanie zapytań chat oraz sprawdzanie kosztów generowania.

## Instalacja

```bash
composer require open-php-router/open-php-router
```

## Konfiguracja

```php
use OpenPhpRouter\OpenRouterClient;

$client = new OpenRouterClient([
    'api_key' => 'your_openrouter_api_key',
    'timeout' => 30, // opcjonalnie
    'referer' => 'https://your-app.com', // opcjonalnie
    'title' => 'your-app-name', // opcjonalnie
]);
```

## Pobieranie listy modeli

```php
use OpenPhpRouter\OpenRouterClient;

$client = new OpenRouterClient(['api_key' => 'your_api_key']);
$models = $client->listModels();

foreach ($models as $model) {
    echo sprintf(
        "%s (%s) - Prompt: $%.4f/1K, Completion: $%.4f/1K\n",
        $model->getName(),
        $model->getProvider(),
        $model->getPromptPricePer1K() ?? 0,
        $model->getCompletionPricePer1K() ?? 0
    );
}
```

## Wysyłanie zapytania chat

```php
use OpenPhpRouter\OpenRouterClient;
use OpenPhpRouter\DTO\ChatData;
use OpenPhpRouter\DTO\MessageData;
use OpenPhpRouter\Enum\RoleType;

$client = new OpenRouterClient(['api_key' => 'your_api_key']);

$chatData = new ChatData(
    messages: [
        MessageData::createUserMessage('Hello, who are you?'),
    ],
    model: 'mistralai/mistral-7b-instruct:free',
    maxTokens: 100,
    temperature: 0.7,
);

$response = $client->chat($chatData);
echo $response->getContent();
```

## Sprawdzanie kosztów generowania

### Metoda 1: Pobieranie kosztów przez ID generowania

```php
// Po wykonaniu zapytania chat
$response = $client->chat($chatData);
$generationId = $response->getId();

// Pobierz szczegółowe informacje o kosztach
$costs = $client->getRequestCosts($generationId);

echo sprintf(
    "Prompt tokens: %d (%.4f USD)\n",
    $costs->getPromptTokens(),
    $costs->getPromptCost()
);

echo sprintf(
    "Completion tokens: %d (%.4f USD)\n",
    $costs->getCompletionTokens(),
    $costs->getCompletionCost()
);

echo sprintf(
    "Total cost: %.4f USD\n",
    $costs->getTotalCost()
);

echo sprintf(
    "Cost per 1K prompt tokens: %.4f USD\n",
    $costs->getPromptCostPer1K()
);

echo sprintf(
    "Cost per 1K completion tokens: %.4f USD\n",
    $costs->getCompletionCostPer1K()
);
```

### Metoda 2: Pobieranie kosztów bezpośrednio z odpowiedzi

```php
$response = $client->chat($chatData);

// Pobierz koszty bezpośrednio z odpowiedzi
$costs = $client->getChatRequestCosts($response);

echo sprintf(
    "ID generowania: %s\n",
    $costs->getId()
);

echo sprintf(
    "Model: %s\n",
    $costs->getModel()
);

echo sprintf(
    "Total cost: %.4f %s\n",
    $costs->getTotalCost(),
    $costs->getCurrency()
);
```

## Przykład z system message

```php
$chatData = new ChatData(
    messages: [
        MessageData::createSystemMessage('You are a helpful assistant.'),
        MessageData::createUserMessage('What is the capital of Poland?'),
    ],
    model: 'mistralai/mistral-7b-instruct:free',
    maxTokens: 50,
);

$response = $client->chat($chatData);
echo $response->getContent();
```

## Dostępne metody

### OpenRouterClient

- `listModels()` - Pobiera listę dostępnych modeli z cenami
- `chat(ChatData $chatData)` - Wysyła zapytanie chat i zwraca odpowiedź
- `getGeneration(string $generationId)` - Pobiera surowe dane o generowaniu
- `getRequestCosts(string $generationId)` - Pobiera koszty dla konkretnego requesta
- `getChatRequestCosts(ChatResponseData $response)` - Pobiera koszty bezpośrednio z odpowiedzi chat

### GenerationCostData

- `getId()` - ID generowania
- `getModel()` - Nazwa modelu
- `getPromptTokens()` - Liczba tokenów prompt
- `getCompletionTokens()` - Liczba tokenów completion
- `getTotalTokens()` - Całkowita liczba tokenów
- `getPromptCost()` - Koszt prompt
- `getCompletionCost()` - Koszt completion
- `getTotalCost()` - Całkowity koszt
- `getCurrency()` - Waluta (domyślnie USD)
- `getPromptCostPer1K()` - Koszt na 1K tokenów prompt
- `getCompletionCostPer1K()` - Koszt na 1K tokenów completion

## Obsługa błędów

```php
use OpenPhpRouter\Exception\OpenRouterException;

try {
    $models = $client->listModels();
} catch (OpenRouterException $e) {
    echo 'Błąd OpenRouter: ' . $e->getMessage();
}
```

## Wymagania

- PHP 8.2+
- cURL extension (zazwyczaj wbudowane w PHP)

## Licencja

MIT License 