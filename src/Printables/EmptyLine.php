<?php

/**
 * This file was taken from the Nginx Config Processor package.
 *
 * (c) Roman Piták <roman@pitak.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Toml\Printables;

class EmptyLine extends Printable
{
    public static function fromString()
    {
        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        return "\n";
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [];
    }
}
