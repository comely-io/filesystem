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

namespace Comely\Filesystem\Local;

use Comely\Filesystem\Directory;
use Comely\Filesystem\Exception\PathException;

/**
 * Class AbstractPath
 * @package Comely\Filesystem\Local
 */
abstract class AbstractPath
{
    public const IS_DIRECTORY = 0x64;
    public const IS_FILE = 0xc8;
    public const IS_LINK = 0x012c;

    /** @var string */
    private $path;
    /** @var null|int */
    private $type;
    /** @var null|Permissions */
    private $permissions;
    /** @var null|bool */
    private $deleted;

    /**
     * AbstractPath constructor.
     * @param string $path
     * @throws PathException
     */
    public function __construct(string $path)
    {
        $path = realpath($path); // Get an absolute real path
        if (!$path) {
            throw new PathException('File or directory does not exist');
        }

        // Type
        if (is_dir($path)) {
            $this->type = self::IS_DIRECTORY;
        } elseif (is_file($path)) {
            $this->type = self::IS_FILE;
        } elseif (is_link($path)) {
            $this->type = self::IS_LINK;
        }
    }

    /**
     * @return int|null
     */
    public function type(): ?int
    {
        return $this->type;
    }

    /**
     * @return AbstractPath
     * @throws PathException
     */
    public function clearStatCache(): self
    {
        clearstatcache(true, $this->path());
        $this->permissions()->reset(); // Reset cached permissions
        return $this;
    }

    /**
     * @return string
     * @throws PathException
     */
    public function path(): string
    {
        if ($this->deleted) {
            throw new PathException('File/directory path has been deleted is no longer valid');
        }

        return $this->path;
    }

    /**
     * @return Permissions
     */
    public function permissions(): Permissions
    {
        if (!$this->permissions) {
            $this->permissions = new Permissions($this);
        }

        return $this->permissions;
    }

    /**
     * @return Directory
     * @throws PathException
     */
    public function parent(): Directory
    {
        return new Directory(dirname($this->path));
    }

    public function delete(bool $recursive = false): void
    {

    }
}