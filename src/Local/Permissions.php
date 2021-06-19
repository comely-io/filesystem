<?php
/*
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

use Comely\Filesystem\Exception\PathOpException;

/**
 * Class Permissions
 * @package Comely\Filesystem\Local
 */
class Permissions
{
    /** @var null|bool */
    private ?bool $readable = null;
    /** @var null|bool */
    private ?bool $writable = null;
    /** @var null|bool */
    private ?bool $executable = null;

    /**
     * Permissions constructor.
     * @param AbstractPath $path
     */
    public function __construct(private AbstractPath $path)
    {
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        $permissions = [];
        foreach (["readable", "writable", "executable"] as $perm) {
            if (is_bool($this->$perm)) {
                $permissions[$perm] = $this->$perm;
            }
        }

        return $permissions;
    }

    /**
     * @return Permissions
     */
    public function reset(): self
    {
        $this->readable = null;
        $this->writable = null;
        $this->executable = null;
        return $this;
    }

    /**
     * @param string $mode
     * @return Permissions
     * @throws PathOpException
     * @throws \Comely\Filesystem\Exception\PathException
     */
    public function chmod(string $mode): self
    {
        if (!preg_match('/^0[0-9]{3}$/', $mode)) {
            throw new \InvalidArgumentException('Invalid chmod argument, expecting octal number as string');
        }

        if (!chmod($this->path->path(), intval($mode, 8))) {
            throw new PathOpException('Cannot change file/directory permissions');
        }

        $this->path->clearStatCache();
        return $this;
    }

    /**
     * @return bool
     */
    public function readable(): bool
    {
        if (!is_bool($this->readable)) {
            $this->readable = is_readable($this->path->path());
        }

        return $this->readable;
    }

    /**
     * @return bool
     */
    public function writable(): bool
    {
        if (!is_bool($this->writable)) {
            $this->writable = is_writable($this->path->path());
        }

        return $this->writable;
    }

    /**
     * @return bool
     */
    public function executable(): bool
    {
        if (!is_bool($this->executable)) {
            $this->executable = is_executable($this->path->path());
        }

        return $this->executable;
    }
}
