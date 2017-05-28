<?php

namespace Toml\Printables;

use Toml\Exception;

class BareKeyValue extends Printable
{
    /**
     * @var string
     */
    protected $key;

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
     * @param string $value
     * @param string $valueQuote
     */
    public function __construct(
        $key,
        $value,
        $valueQuote = '"'
    ) {
        $this->key = $key;
        $this->value = $value;
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
        if (preg_match('/([A-Za-z0-9_\-]+)[\t ]*=[\t ]*(["\'])?(.*)$/', $tomlString, $matches)) {
            $rQuote = '';
            if ($matches[2] !== '') {
                $rQuote = substr($matches[3], -1);
                if ($matches[2] !== $rQuote) {
                    throw new Exception(sprintf('Value "%s" for key "%s" incorrectly quoted!', $matches[3], $matches[1]));
                }
            }

            // Remove closing quote from value.
            $value = rtrim($matches[3], $matches[2]);

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

            return new static(
                $matches[1],
                $value,
                $matches[2]
            );
        }

        throw new Exception(sprintf('Incorrectly formatted bare key/value string: "%s"', $tomlString));
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
            $this->key .
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
