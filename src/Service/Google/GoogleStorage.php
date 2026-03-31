<?php

declare(strict_types=1);

namespace App\Service\Google;

use Google\Cloud\Storage\StorageClient;

final class GoogleStorage
{
    protected StorageClient $gsClient;

    public function getStorageClient(): StorageClient
    {
        if (!isset($this->gsClient)) {
            $this->gsClient = new StorageClient();
        }

        return $this->gsClient;
    }
}
