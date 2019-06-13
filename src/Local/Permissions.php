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

/**
 * Class Permissions
 * @package Comely\Filesystem\Local
 */
class Permissions
{
    /** @var AbstractPath */
    private $path;
    /** @var null|bool */
    private $read;
    /** @var null|bool */
    private $write;
    /** @var null|bool */
    private $execute;

    /**
     * Permissions constructor.
     * @param AbstractPath $path
     */
    public function __construct(AbstractPath $path)
    {
        $this->path = $path;
    }

    /**
     * @return Permissions
     */
    public function reset(): self
    {
        $this->read = null;
        $this->write = null;
        $this->execute = null;
        return $this;
    }

    /**
     * @return bool
     */
    public function read(): bool
    {
        if (!is_bool($this->read)) {
            $this->read = is_readable($this->path->path());
        }

        return $this->read;
    }

    /**
     * @return bool
     */
    public function write(): bool
    {
        if (!is_bool($this->write)) {
            $this->write = is_writable($this->path->path());
        }

        return $this->write;
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        if (!is_bool($this->execute)) {
            $this->execute = is_executable($this->path->path());
        }

        return $this->execute;
    }
}