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

abstract class Printable
{
    /**
     * Pretty print with indentation.
     *
     * @param $indentLevel
     * @param int $spacesPerIndent
     *
     * @return string
     */
    abstract public function prettyPrint($indentLevel, $spacesPerIndent = 4);

    /**
     * Converts a printable to an array.
     *
     * @return array
     */
    abstract public function toArray();

    /**
     * Convert a boolean to TOML string notation.
     *
     * @param $value
     * @return string
     */
    public function boolToString($value) {
        if (is_bool($value)) {
            return ($value === true) ? 'true' : 'false';
        }
        return $value;
    }

    public function __toString()
    {
        return $this->prettyPrint(0);
    }
}
