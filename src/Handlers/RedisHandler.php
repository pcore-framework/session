<?php

namespace PCore\Session\Handlers;

use PCore\Redis\{Redis, RedisManager};
use PCore\Session\Exceptions\SessionException;
use PCore\Utils\Traits\AutoFillProperties;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use SessionHandlerInterface;

/**
 * Class RedisHandler
 * @package PCore\Session\Handlers
 * @github https://github.com/pcore-framework/session
 */
class RedisHandler implements SessionHandlerInterface
{

    use AutoFillProperties;

    /**
     * @var Redis
     */
    protected Redis $handler;

    /**
     * @var string
     */
    protected string $connection;

    /**
     * @var int
     */
    protected int $expire = 3600;

    /**
     * @param array $options
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function __construct(array $options = [])
    {
        $this->fillProperties($options);
        if (!class_exists('PCore\Redis\RedisManager')) {
            throw new SessionException('Вам нужно будет установить пакет Redis с помощью `composer require pcore/redis`');
        }
        /** @var RedisManager $manager */
        $manager = make(RedisManager::class);
        $this->handler = $manager->connection($this->connection);
    }

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function close(): bool
    {
        return true;
    }

    /**
     * @param string $id
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function destroy(string $id): bool
    {
        return (bool)$this->handler->del($id);
    }

    /**
     * @param int $max_lifetime
     * @return int|false
     */
    #[\ReturnTypeWillChange]
    public function gc(int $max_lifetime): int|false
    {
        return 1;
    }

    /**
     * @param string $path
     * @param string $name
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function open(string $path, string $name)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function read(string $id): string|false
    {
        if ($data = $this->handler->get($id)) {
            return (string)$data;
        }
        return false;
    }

    /**
     * @param string $id
     * @param string $data
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function write(string $id, string $data): bool
    {
        return (bool)$this->handler->set($id, $data, $this->expire);
    }

}