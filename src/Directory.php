<?php
/**
 * This file is a part of "comely-io/filesystem" package.
 * https://github.com/comely-io/filesystem
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comely-io/filesystem/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Filesystem;

use Comely\Filesystem\Exception\PathException;
use Comely\Filesystem\Exception\PathOpException;
use Comely\Filesystem\Exception\PathPermissionException;
use Comely\Filesystem\Local\AbstractPath;
use Comely\Filesystem\Local\DirFactory;

/**
 * Class Directory
 * @package Comely\Filesystem
 */
class Directory extends AbstractPath
{
    /** @var null|DirFactory */
    private $factory;

    /**
     * Directory constructor.
     * @param string $path
     * @throws PathException
     */
    public function __construct(string $path)
    {
        parent::__construct($path);
        if ($this->type() !== self::IS_DIRECTORY) {
            throw new PathException('Cannot instantiate path as Directory object');
        }
    }

    /**
     * @param string $path
     * @return string
     * @throws PathException
     */
    public function suffix(string $path): string
    {
        $sep = preg_quote(DIRECTORY_SEPARATOR, "/");
        if (!preg_match('/^(' . $sep . '?[\w\-\.]+' . $sep . ')+$/', $path)) {
            throw new \InvalidArgumentException('Invalid suffix path');
        }

        return $this->path() . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Checks if file or directory exists within the given path and returns its type otherwise NULL
     * @param string $child
     * @return int|null
     * @throws PathException
     */
    public function has(string $child): ?int
    {
        $child = $this->suffix($child);
        if (file_exists($child)) {
            if (is_dir($child)) {
                return self::IS_DIRECTORY;
            } elseif (is_file($child)) {
                return self::IS_FILE;
            } elseif (is_link($child)) {
                return self::IS_LINK;
            }
        }

        return null;
    }

    /**
     * @param string $child
     * @return File
     * @throws PathException
     */
    public function file(string $child): File
    {
        return new File($this->suffix($child));
    }

    /**
     * @param string $child
     * @return Directory
     * @throws PathException
     */
    public function dir(string $child): Directory
    {
        return new Directory($this->suffix($child));
    }

    /**
     * @param string $child
     * @return Directory|File
     * @throws PathException
     */
    public function child(string $child)
    {
        $child = $this->suffix($child);
        $type = $this->has($child);
        switch ($type) {
            case self::IS_DIRECTORY:
                return new Directory($child);
            case self::IS_FILE:
                return new File($child);
        }

        throw new PathException('No such file or directory exists');
    }

    /**
     * @param bool $absolutePaths
     * @param int $sort
     * @return array
     * @throws PathException
     * @throws PathOpException
     * @throws PathPermissionException
     */
    public function scan(bool $absolutePaths = false, int $sort = SCANDIR_SORT_NONE): array
    {
        if (!$this->permissions()->read()) {
            throw new PathPermissionException('Cannot scan directory; Directory is not readable');
        }

        $directoryPath = $this->path();
        $final = [];
        $scan = scandir($directoryPath, $sort);
        if (!is_array($scan)) {
            throw new PathOpException('Failed to scan directory');
        }

        foreach ($scan as $file) {
            if (in_array($file, [".", ".."])) {
                continue; // Skip dots
            }

            $final[] = $absolutePaths ? $directoryPath . DIRECTORY_SEPARATOR . $file : $file;
        }

        return $final;
    }

    /**
     * @param string $pattern
     * @param bool $absolutePaths
     * @param int $flags
     * @return array
     * @throws PathException
     * @throws PathOpException
     * @throws PathPermissionException
     */
    public function glob(string $pattern, bool $absolutePaths = false, int $flags = 0): array
    {
        if (!$this->permissions()->read()) {
            throw new PathPermissionException('Cannot use glob; Directory is not readable');
        }

        if (!preg_match('/^[\w\*\-\.]+$/', $pattern)) {
            throw new \InvalidArgumentException('Unacceptable glob pattern');
        }

        $directoryPath = $this->path() . DIRECTORY_SEPARATOR;
        $final = [];
        $glob = glob($directoryPath . $pattern, $flags);
        if (!is_array($glob)) {
            throw new PathOpException('Directory glob failed');
        }

        foreach ($glob as $file) {
            if (in_array($file, [".", ".."])) {
                continue; // Skip dots
            }

            $final[] = $absolutePaths ? $directoryPath . $file : $file;
        }

        return $final;
    }

    /**
     * @return DirFactory
     */
    public function create(): DirFactory
    {
        if (!$this->factory) {
            $this->factory = new DirFactory($this);
        }

        return $this->factory;
    }

    /**
     * @return DirFactory
     */
    public function new(): DirFactory
    {
        return $this->create();
    }

    /**
     * @return int
     * @throws PathException
     * @throws PathOpException
     * @throws PathPermissionException
     */
    protected function findSizeInBytes(): int
    {
        $sizeInBytes = 0;
        $list = $this->scan(true);
        foreach ($list as $file) {
            if (is_dir($file)) {
                $sizeInBytes += (new Directory($file))->size();
                continue;
            }

            $fileSize = filesize($file);
            if (!is_int($fileSize)) {
                throw new PathOpException(sprintf('Could not find size of file "%s"', basename($file)));
            }

            $sizeInBytes += $fileSize;
        }

        return $sizeInBytes;
    }
}