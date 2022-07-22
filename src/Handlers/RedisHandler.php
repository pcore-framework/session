<?php

namespace PCore\Session\Handlers;

use PCore\Redis\{Redis};
use PCore\Utils\Traits\AutoFillProperties;
use Psr\Container\ContainerExceptionInterface;
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
    protected string $connector;

    /**
     * @var string
     */
    protected string $host = '127.0.0.1';

    /**
     * @var int
     */
    protected int $port = 6379;

    /**
     * @var int
     */
    protected int $expire = 3600;

    public function __construct(array $options = [])
    {
        $this->fillProperties($options);
        $this->handler = new Redis(new $this->connector($this->host, $this->port));
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