<?php

declare(strict_types=1);

namespace PCore\Session;

/**
 * Class ConfigProvider
 * @package PCore\Session
 * @github https://github.com/pcore-framework/session
 */
class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'publish' => [
                [
                    'name' => 'session',
                    'source' => __DIR__ . '/../publish/session.php',
                    'destination' => dirname(__DIR__, 4) . '/config/session.php'
                ]
            ]
        ];
    }
}