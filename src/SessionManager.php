<?php

namespace PCore\Session;

/**
 * Class SessionManager
 * @package PCore\Session
 * @github https://github.com/pcore-framework/session
 */
class SessionManager
{

    /**
     * @var array
     */
    protected array $handlers = [];

    public function __construct(
        protected array $config = []
    )
    {
    }

    /**
     * @param string|null $name
     * @return Session
     */
    public function create(?string $name = null): Session
    {
        $name ??= $this->config['default'];
        if (!isset($this->handlers[$name])) {
            $config = $this->config['stores'][$name];
            $handler = $config['handler'];
            $options = $config['options'];
            $this->handlers[$name] = new $handler($options);
        }
        return new Session($this->handlers[$name]);
    }

}