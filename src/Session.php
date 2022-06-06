<?php

declare(strict_types=1);

namespace PCore\Session;

use InvalidArgumentException;
use PCore\Session\Exceptions\SessionException;
use PCore\Utils\Arr;
use SessionHandlerInterface;

/**
 * Class Session
 * @package PCore\Session
 * @github https://github.com/pcore-framework/session
 */
class Session
{

    /**
     * Идентификатор сеанса.
     *
     * @var string
     */
    protected string $id = '';

    /**
     * Данные сеанса.
     *
     * @var array
     */
    protected array $data = [];

    /**
     * @var bool
     */
    protected bool $started = false;

    public function __construct(protected SessionHandlerInterface $sessionHandler)
    {
    }

    /**
     * @param string|null $id
     * @return void
     */
    public function start(?string $id = null): void
    {
        if ($this->started) {
            throw new SessionException('Сеанс не может быть запущен повторно.');
        }
        $this->id = ($id && $this->isValidId($id)) ? $id : \session_create_id();
        if ($data = $this->sessionHandler->read($this->id)) {
            $this->data = (array)(@\unserialize($data) ?: []);
        }
        $this->started = true;
    }

    /**
     * @return void
     */
    public function save(): void
    {
        $this->sessionHandler->write($this->id, serialize($this->data));
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->sessionHandler->close();
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return Arr::has($this->data, $key);
    }

    /**
     * @param string $key
     * @param        $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null): mixed
    {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return array
     */
    public function set(string $key, $value): array
    {
        return Arr::set($this->data, $key, $value);
    }

    /**
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        $data = $this->get($key, $default);
        $this->remove($key);
        return $data;
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key): void
    {
        Arr::forget($this->data, $key);
    }

    /**
     * @return void
     */
    public function destroy(): void
    {
        $this->sessionHandler->destroy($this->id);
        $this->data = [];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id ?: throw new SessionException('Сеанс не запущен.');
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId(string $id): void
    {
        if (!$this->isValidId($id)) {
            throw new InvalidArgumentException('Длина идентификатора сеанса должна быть ровна 40.');
        }
        $this->id = $id;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    protected function isValidId(string $id): bool
    {
        return \ctype_alnum($id);
    }

}