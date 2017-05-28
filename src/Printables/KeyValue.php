<?php

namespace Toml\Printables;

use Toml\Exception;
use Toml\Lexer\Text;

class KeyValue extends Printable
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $keyQuote;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var mixed
     */
    protected $valueQuote;

    /**
     * @var Comment
     */
    protected $comment;


    /**
     * KeyValue constructor.
     *
     * @param string $key
     * @param string $keyQuote
     * @param string $value
     * @param string $valueQuote
     */
    public function __construct(
        $key,
        $value,
        $keyQuote = '',
        $valueQuote = '"'
    ) {
        $this->key = $key;
        $this->value = $value;
        $this->keyQuote = $keyQuote;
        $this->valueQuote = $valueQuote;
    }

    /**
     * @param string $tomlString
     *
     * @return static
     *
     * @throws Exception
     */
    public static function fromString($tomlString)
    {
        if (preg_match('/(["\'])(.*)(["\'])[\t ]*=[\t ]*(["\'])?(.*)/', $tomlString, $matches)) {
            if ($matches[1] !== $matches[3]) {
                throw new Exception(sprintf('Key "%s" incorrectly quoted!', $matches[2]));
            }

            $rQuote = '';
            if ($matches[4] !== '') {
                $rQuote = substr($matches[5], -1);
                if ($matches[4] !== $rQuote) {
                    throw new Exception(sprintf('Value "%s" for key "%s" incorrectly quoted!', $matches[5], $matches[2]));
                }
            }

            // Remove closing quote from value.
            $value = rtrim($matches[5], $rQuote);

            // TODO: check value and cast to int, float, date, ...
            if ($rQuote === '') {
                switch (true) {
                    case is_float($value):
                        $value = (float)$value;
                        break;

                    case preg_match('/\d+/', $value) === 1:
                        $value = (int)$value;
                        break;

                    case $value === 'true';
                        $value = true;
                        break;

                    case $value === 'false';
                        $value = false;
                        break;

                    case ($date = date_create($value)) !== false:
                        $value = $date;
                        break;

                    case is_string($value):
                        throw new Exception(sprintf('Unquoted string value "%s" for key "%s"', $value, $matches[2]));
                }
            }

            $keyVal = new static(
                $matches[2],
                $value,
                $matches[1],
                $matches[4]
            );

            /*if (strpos($matches[7], '#')) {
                $keyVal->setComment(Comment::fromPlainString($tomlString));
            }*/

            return $keyVal;
        }

        throw new Exception(sprintf('Incorrectly formatted key/value string: "%s"', $tomlString));
    }

    /**
     * Set the associated Comment object for this Directive.
     *
     * This will overwrite the existing comment.
     *
     * @param Comment $comment
     * @return $this
     */
    public function setComment(Comment $comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * Print out the key-value pair.
     *
     * @param $indentLevel
     *
     * @param int $spacesPerIndent
     *
     * @return string
     */
    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        $indent = str_repeat(str_repeat(' ', $spacesPerIndent), $indentLevel);

        return $indent .
            $this->keyQuote .
            $this->key .
            $this->keyQuote .
            ' = ' .
            $this->valueQuote .
            $this->boolToString($this->value) .
            $this->valueQuote .
            "\n"
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [$this->key => $this->value];
    }
}
