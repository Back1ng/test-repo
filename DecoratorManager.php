<?php

namespace src\Decorator;

use DateTime;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use src\Integration\DataProvider;

// Зачем наследоваться от провайдера данных?
class DecoratorManager extends DataProvider
{
    // не указаны типы переменных
    public $cache;
    public $logger;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param CacheItemPoolInterface $cache
     */
    // ведь его можно принять первым аргументом
    public function __construct($host, $user, $password, CacheItemPoolInterface $cache)
    {
        parent::__construct($host, $user, $password);
        $this->cache = $cache;
    }

    // Здесь лучше вернуть $this, чтобы организовать цепочку вызовов
    // Или как вариант сделать установку логгера через конструктор
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     * 
     *  Не указан возвращаемый тип данных, что у нас должно быть в инпуте?
     *  Не очевидное наименование переменной
     */
    public function getResponse(array $input)
    {
        try {
            $cacheKey = $this->getCacheKey($input);
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            // Делаем запросы в провайдере данных?
            $result = parent::get($input);

            $cacheItem
                ->set($result)
                ->expiresAt(
                    (new DateTime())->modify('+1 day')
                );

            return $result;
        } catch (Exception $e) {
            // Возможно здесь стоит передать ошибку на уровень выше, если он есть
            $this->logger->critical('Error');
        }

        return [];
    }

    // Возвращаемый тип?
    public function getCacheKey(array $input)
    {
        return json_encode($input);
    }
}