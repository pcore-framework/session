<?php

declare(strict_types=1);

namespace PCore\Session\Handlers;

use Closure;
use Exception;
use FilesystemIterator;
use Generator;
use PCore\Utils\Traits\AutoFillProperties;
use SessionHandlerInterface;
use SplFileInfo;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function is_file;
use function mkdir;
use function random_int;
use function rtrim;
use function time;
use function unlink;

/**
 * Class FileHandler
 * @package PCore\Session\Handlers
 * @github https://github.com/pcore-framework/session
 */
class FileHandler implements SessionHandlerInterface
{

    use AutoFillProperties;

    /**
     * @var string
     */
    protected string $path = '/tmp';

    /**
     * @var int
     */
    protected int $gcDivisor = 100;

    /**
     * @var int
     */
    protected int $gcProbability = 1;

    /**
     * @var int
     */
    protected int $gcMaxLifetime = 1440;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->fillProperties($options);
        !is_dir($this->path) && mkdir($this->path, 0755, true);
    }

    /**
     * @param int $maxLifeTime
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function gc($maxLifeTime): int|false
    {
        try {
            $number = 0;
            $now = time();
            $files = $this->findFiles($this->path, function (SplFileInfo $item) use ($maxLifeTime, $now) {
                return $now - $maxLifeTime > $item->getMTime();
            });
            foreach ($files as $file) {
                $this->unlink($file->getPathname());
                $number++;
            }
            return $number;
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     * Найти файл
     *
     * @param string $root
     * @param Closure $filter
     * @return Generator
     */
    protected function findFiles(string $root, Closure $filter): Generator
    {
        $items = new FilesystemIterator($root);
        /** @var SplFileInfo $item */
        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                yield from $this->findFiles($item->getPathname(), $filter);
            } else {
                if ($filter($item)) {
                    yield $item;
                }
            }
        }
    }

    /**
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        try {
            return $this->unlink($this->getSessionFile($id));
        } catch (Exception) {
            return false;
        }
    }

    /**
     * После определения того, существует ли файл потом стирать
     *
     * @param string $file
     * @return bool
     */
    private function unlink(string $file): bool
    {
        return is_file($file) && unlink($file);
    }

    /**
     * @param $id
     * @return string
     */
    protected function getSessionFile(string $id): string
    {
        return rtrim($this->path, '/\\') . '/sess_' . $id;
    }

    /**
     * @param string $id
     * @return false|string
     */
    #[\ReturnTypeWillChange]
    public function read($id): string|false
    {
        $sessionFile = $this->getSessionFile($id);
        if (file_exists($sessionFile)) {
            return file_get_contents($sessionFile) ?: '';
        }
        return '';
    }

    /**
     * @param string $id
     * @param string $data
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function write($id, $data): bool
    {
        return (bool)file_put_contents($this->getSessionFile($id), $data, LOCK_EX);
    }

    /**
     * @return bool
     * @throws Exception
     */
    #[\ReturnTypeWillChange]
    public function close(): bool
    {
        if (random_int(1, $this->gcDivisor) <= $this->gcProbability) {
            $this->gc($this->gcMaxLifetime);
        }
        return true;
    }

    /**
     * @param string $id
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function destroy($id): bool
    {
        return $this->unlink($this->getSessionFile($id));
    }

    /**
     * @param string $path
     * @param string $name
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function open(string $path, string $name): bool
    {
        return true;
    }

}