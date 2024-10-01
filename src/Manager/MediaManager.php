<?php

namespace App\Manager;

use Faker\Provider\UserAgent;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

final readonly class MediaManager
{
    public function __construct(
        private LoggerInterface $logger,
        private Client $guzzleClient = new Client(['cookies' => true]),
    ) {
    }

    public function createMediaObjectFileFromUrl(string $path, bool|File $defaultFile = false): ?File
    {
        if ($defaultFile instanceof File) {
            return $defaultFile;
        }

        try {
            $file = null;
            $tmpFileName = tempnam(sys_get_temp_dir(), '');
            $pathInfo = pathinfo($path);
            $urlContent = $this->getUrlContent($path);

            if (false !== $tmpFileName && false !== $urlContent) {
                file_put_contents(
                    $tmpFileName,
                    $urlContent
                );

                $type = MimeTypes::getDefault()->guessMimeType($tmpFileName);

                $basename = $pathInfo['filename'];

                $file = new UploadedFile($tmpFileName, $basename, $type);
            }
        } catch (\Exception $e) {
            $this->logger->warning(
                sprintf('Unable to create uploaded file from path %s', $path),
                ['details' => $e,]
            );

            return null;
        }

        return $file;
    }

    private function getUrlContent(string $url): bool|string
    {
        try {
            $response = $this->guzzleClient->get(
                $url,
                [
                    'allow_redirects' => true,
                    'headers' => [
                        'User-Agent' => UserAgent::userAgent(),
                    ],
                ]
            );
            if ($contents = $response->getBody()->getContents()) {
                return $contents;
            } else {
                return false;
            }
        } catch (ClientException|RequestException $e) {
            return false;
        }
    }
}
