<?php

declare(strict_types=1);

namespace src\Decorator;

use DateTime;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use src\Integration\DataProvider;

final class DecoratorManager
{
    private LoggerInterface $logger;

    /**
     * @param DataProvider $dataProvider
     * @param ClientInterface $client
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(
        public readonly DataProvider $dataProvider,
        public readonly ClientInterface $client,
        public readonly CacheItemPoolInterface $cache,
    ) {}

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse(array $input): array
    {
        try {
            $cacheKey = $this->getCacheKey($input);
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
            
            $result = $this->client->get($input);

            $cacheItem
                ->set($result)
                ->expiresAt(
                    (new DateTime())->modify('+1 day')
                );

            return $result;
        } catch (Exception $e) {
            $this->logger->critical('Error');

            throw $e;
        }

        return [];
    }

    private function getCacheKey(array $input): string
    {
        return json_encode($input);
    }
}