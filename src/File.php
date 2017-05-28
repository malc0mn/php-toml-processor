<?php

/**
 * This file is part of the Nginx Config Processor package.
 *
 * (c) Roman Piták <roman@pitak.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Toml;

use Toml\Lexer\Lexer;

class File extends Lexer
{
    /**
     * @var string
     */
    private $inFilePath;

    /**
     * @param $filePath string Name of the conf file (or full path).
     *
     * @throws Exception
     */
    public function __construct($filePath)
    {
        $this->inFilePath = $filePath;

        $contents = @file_get_contents($this->inFilePath);

        if ($contents === false) {
            throw new Exception(sprintf('Cannot read file "%s".', $this->inFilePath));
        }

        parent::__construct($contents);
    }
}
