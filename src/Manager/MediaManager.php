<?php

namespace App\Manager;

use App\Enum\FileSize;
use Faker\Provider\UserAgent;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use League\Flysystem\FilesystemOperator;
use Liip\ImagineBundle\Service\FilterService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

final readonly class MediaManager
{
    public function __construct(
        private FilesystemOperator $eventsStorage,
        private FilesystemOperator $customEventsStorage,
        private FilterService $imagineFilterService,
        private LoggerInterface $logger,
        private string $projectDir,
        private Client $guzzleClient = new Client(['cookies' => true]),
    ) {
    }

    public function warmupEventCache(string $fileName, ?FileSize $fileSize = null): File
    {
        return $this->warmupFileCache($fileName, $this->eventsStorage, 'images/events', 'events', $fileSize);
    }

    public function warmupCustomEventCache(string $fileName, ?FileSize $fileSize = null): File
    {
        return $this->warmupFileCache($fileName, $this->customEventsStorage, 'images/custom_events', 'custom_events', $fileSize);
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
            }

            return false;
        } catch (ClientException|RequestException $e) {
            return false;
        }
    }

    private function warmupFileCache(string $fileName, FilesystemOperator $storage, string $storageBasePath, string $filter, ?FileSize $fileSize = null): File
    {
        $localFileLocation = null === $fileSize || FileSize::DEFAULT === $fileSize ?
            sprintf('%s/public/%s/%s', $this->projectDir, $storageBasePath, $fileName) :
            sprintf('%s/public/%s/%s', $this->projectDir, $storageBasePath.'/'.$fileSize->value, $fileName);

        if (!file_exists($localFileLocation)) {
            $directory = dirname($localFileLocation);

            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $localFileStream = fopen($localFileLocation, 'a');

            $remoteFileLocation = $fileName;

            if (!str_ends_with($fileName, '.svg')) {
                if (!$storage->fileExists($remoteFileLocation)) {
                    $this->imagineFilterService->warmUpCache($fileName, null === $fileSize || FileSize::DEFAULT === $fileSize ? $filter : $filter.'_'.$fileSize->value);
                }
            }

            $fileStream = $storage->readStream($remoteFileLocation);

            if ($localFileStream !== false) {
                stream_copy_to_stream($fileStream, $localFileStream);
                fclose($fileStream);
                fclose($localFileStream);
            }
        }

        return new File($localFileLocation);
    }
}
