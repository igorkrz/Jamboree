<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

use function fclose;
use function fsockopen;
use function parse_url;
use function str_starts_with;

final class HealthCheckController extends AbstractController
{
    #[Route('/health-check', name: 'health_check', methods: ['GET'])]
    public function __invoke(
        EntityManagerInterface $entityManager,
        #[Autowire(env: 'MESSENGER_TRANSPORT_DSN')]
        string $messengerTransportDsn,
    ): JsonResponse {
        $services = [
            'database' => 'up',
            'redis' => 'up',
        ];

        $failedServices = [];

        try {
            $connection = $entityManager->getConnection();
            $connection->executeQuery($connection->getDatabasePlatform()->getDummySelectSQL());
        } catch (Throwable) {
            $services['database'] = 'down';
            $failedServices[] = 'database';
        }

        try {
            if ($messengerTransportDsn && str_starts_with($messengerTransportDsn, 'redis://')) {
                $urlComponents = parse_url($messengerTransportDsn);
                $host = $urlComponents['host'] ?? '127.0.0.1';
                $port = $urlComponents['port'] ?? 6379;

                $socket = @fsockopen($host, (int) $port, $errno, $errstr, 2);
                if (!$socket) {
                    throw new RuntimeException("Could not connect to Redis: $errstr");
                }
                fclose($socket);
            }
        } catch (Throwable) {
            $services['redis'] = 'down';
            $failedServices[] = 'redis';
        }

        if (empty($failedServices)) {
            return $this->json('ok');
        }

        return $this->json([
            'status' => 'error',
            'failed_services' => $failedServices,
            'details' => $services,
        ], 503);
    }
}
