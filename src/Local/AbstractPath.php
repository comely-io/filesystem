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

use Comely\Filesystem\Exception\PathException;

/**
 * Class AbstractPath
 * @package Comely\Filesystem\Local
 */
abstract class AbstractPath
{
    /** @var string */
    private $path;
    /** @var null|Permissions */
    private $permissions;

    public function __construct(string $path)
    {
        $path = realpath($path); // Get an absolute real path
        if (!$path) {
            throw new PathException('File or directory does not exist');
        }


    }

    /**
     * @return string
     */
    public function path(): string
    {
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
}