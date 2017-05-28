<?php

namespace Toml;

use Toml\Lexer\Lexer;
use Toml\Lexer\Text;
use Toml\Printables\Arrey;
use Toml\Printables\BareKeyValue;
use Toml\Printables\Comment;
use Toml\Printables\EmptyLine;
use Toml\Printables\KeyValue;
use Toml\Printables\Printable;
use Toml\Printables\Table;

class Toml extends Printable
{
    /**
     * @var Printable[]
     */
    private $printables = [];

    /**
     * Create new Scope from the configuration string.
     *
     * @param Lexer $tomlString
     *
     * @return self
     *
     * @throws Exception
     */
    public static function fromString(Lexer $tomlString)
    {
        $toml = new static();

        // Because the current token always gets filled with the lookahead token
        // when calling moveNext(), we have to call this method twice to do a
        // proper initialisation.
        $tomlString->moveNext();
        // Start our parsing...
        while ($tomlString->moveNext()) {
            switch($tomlString->token['type']) {
                case Lexer::T_COMMENT:
                    $toml->addPrintable(
                        Comment::fromString($tomlString->token['value'])
                    );
                break;

                case Lexer::T_EMPTY_LINE:
                    $toml->addPrintable(
                        EmptyLine::fromString()
                    );
                    break;

                case Lexer::T_BARE_KEY_VALUE:
                    $toml->addPrintable(
                        BareKeyValue::fromString($tomlString->token['value'])
                    );
                    break;

                case Lexer::T_KEY_VALUE:
                    $toml->addPrintable(
                        KeyValue::fromString($tomlString->token['value'])
                    );
                    break;

                case Lexer::T_ARRAY:
                    $toml->addPrintable(
                        Arrey::fromString($tomlString, $tomlString->token['value'])
                    );
                    break;

                case Lexer::T_TABLE:
                    $toml->addPrintable(
                        Table::fromString($tomlString)
                    );
                    break;
            }
        }

        return $toml;
    }

    /**
     * Create new Scope from a file.
     *
     * @param $filePath
     *
     * @return self
     */
    public static function fromFile($filePath)
    {
        return self::fromString(new File($filePath));
    }

    /**
     * Add printable element.
     *
     * @param Printable $printable
     */
    private function addPrintable(Printable $printable)
    {
        $this->printables[] = $printable;
    }

    /**
     * Write a Toml file.
     *
     * @param $filePath
     *
     * @throws Exception
     */
    public function saveToFile($filePath)
    {
        $handle = @fopen($filePath, 'w');
        if ($handle) {
            throw new Exception(sprintf('Cannot open file "%s" for writing.', $filePath));
        }

        $bytesWritten = @fwrite($handle, (string)$this);
        if ($bytesWritten === false) {
            fclose($handle);
            throw new Exception(sprintf('Cannot write to file "%s".', $filePath));
        }

        $closed = @fclose($handle);
        if ($closed === false) {
            throw new Exception(sprintf('Cannot close file handle for "%s".', $filePath));
        }
    }

    /**
     * Pretty print with indentation.
     *
     * @param $indentLevel
     *
     * @param int $spacesPerIndent
     *
     * @return string
     */
    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        $resultString = "";
        foreach ($this->printables as $printable) {
            $resultString .= $printable->prettyPrint($indentLevel + 1, $spacesPerIndent);
        }

        return $resultString;
    }

    /**
     * Allow casting to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->prettyPrint(-1);
    }

    public function toArray() {
        $data = [];
        foreach ($this->printables as $printable) {
            $arr = $printable->toArray();
            if ($arr !== null) {
                $data = array_merge($data, $arr);
            }
        }
        return $data;
    }
}
