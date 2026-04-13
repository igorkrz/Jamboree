<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Dto\AiEventDto;
use App\Entity\Dto\EventDto;
use AutoMapperPlus\AutoMapperInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\RawResultInterface;
use Throwable;

use function json_decode;
use function preg_replace;
use function trim;

final readonly class AiEventParser
{
    public function __construct(
        private AgentInterface $agent,
        private AutoMapperInterface $mapper,
        private LoggerInterface $logger,
    ) {
    }

    public function parse(EventDto $input): EventDto
    {
        $prompt = <<<PROMPT
Role: Professional Event Data Extractor.
Task: Extract structured event details from text.

Context: 
- Location and Date are often embedded in the "Event Name".
- Croatian context is common.
- Reference locations: "Tvornica Kulture", "Vintage Industrial Bar", "Klub Močvara", "Boogaloo Zagreb", "Dom Sportova", "Arena Zagreb", Klub Sax!, "Šalata".
- Reference cities: "Zagreb", "Split", "Rijeka", "Osijek".
- If locations have similar names like "Tvornica" map it to "Tvornica Kulture", the same goes for "Močvara" - map it to "Klub Močvara", "VIB" means "Vintage Industrial Bar", etc..

Extraction Rules:
1. "holdingDate": Extract and return in ISO 8601 format (YYYY-MM-DD). If only DD.MM is provided, assume current or next year based on logical context. 
2. "venue": Extract the specific venue/building name. If not explicitly found, look for it in the event name.
3. "city": Extract the city name.
4. "name": Extract the main title of the event, removing dates or locations if they are clearly separate.
5. "artists": List all performing artists or bands as strings in an array.
6. "description": A concise summary of the event.

Return ONLY valid JSON with this exact structure:
{
  "name": string|null,
  "description": string|null,
  "holdingDate": string|null,
  "artists": string[],
  "location": {
      venue: string|null,
      city: string|null,
      addressLine: string|null,
      zipCode: string|null,
      country: string|null,
  }
}.
PROMPT;

        $messages = new MessageBag(
            Message::forSystem($prompt),
            Message::ofUser((string) $input),
        );

        try {
            $output = $this->agent->call($messages);
            $rawResult = $output->getRawResult();

            if (!$rawResult instanceof RawResultInterface) {
                throw new RuntimeException('Unexpected output type');
            }

            $rawArray = $rawResult->getData();
            if (isset($rawArray['choices'][0]['message']['content'])) {
                $content = $rawArray['choices'][0]['message']['content'];
                $json = preg_replace('/^```json\n|\n```$/', '', trim($content));
                $data = json_decode($json, false);
            } else {
                throw new RuntimeException('Failed to parse AI response into object or array');
            }

            /** @var AiEventDto $aiEventDto */
            $aiEventDto = $this->mapper->map($data, AiEventDto::class);
            $eventDto = $this->mapper->mapToObject($aiEventDto, $input);
        } catch (Throwable $e) {
            $this->logger->error('Failed to parse AI response', ['exception' => $e]);
            throw $e;
        }

        $this->logger->info('Parsed event details', ['event' => (string) $eventDto]);

        return $eventDto;
    }
}
