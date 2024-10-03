<?php

declare(strict_types=1);

namespace App\Api\Decoder;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

final class MultipartDecoder implements DecoderInterface
{
    public const FORMAT = 'multipart';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * @param array<string, mixed> $context
     * @return ?array<string, mixed>
     */
    public function decode(string $data, string $format, array $context = []): ?array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return null;
        }

        $data = json_encode($request->request->all());

        if ($data === false) {
            return null;
        }

        return json_decode($data, true) + $request->files->all();
    }

    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }
}
