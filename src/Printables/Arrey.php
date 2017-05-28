<?php

namespace Toml\Printables;

use Toml\Exception;
use Toml\Lexer\Lexer;
use Toml\Lexer\Text;

class Arrey extends Printable
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
     * @var Comment
     */
    protected $comment;


    /**
     * KeyValue constructor.
     *
     * @param string $key
     * @param string $keyQuote
     * @param string $value
     */
    public function __construct(
        $key,
        $value,
        $keyQuote = ''
    ) {
        $this->key = $key;
        $this->value = $value;
        $this->keyQuote = $keyQuote;
    }

    /**
     * @param Lexer $tomlString
     * @param string $line
     *
     * @return static
     *
     * @throws Exception
     */
    public static function fromString(Lexer $tomlString, $line)
    {
        if (preg_match('/(["\'])?(.*)(["\'])?[\t ]*=[\t ]*(\[.*)/', $line, $matches)) {
            if ($matches[1] !== $matches[3]) {
                throw new Exception(sprintf('Key "%s" incorrectly quoted!', $matches[2]));
            }

            $error = false;
            $value = json_decode($matches[4]);
            if ($value === null) {
                $token = $tomlString->token;
                // See if we have a multiline array!
                $tomlString->skipUntil(Lexer::T_ARRAY_END);
                $tomlString->moveNext();

                $input = $tomlString->getInputUntilPosition(
                    $tomlString->token['position'] - $token['position'] + 1,
                    $token['position']
                );
                if (preg_match('/(\[.*\])/s', $input, $multilineMatches)) {
                    $value = json_decode($multilineMatches[1]);
                    if ($value === null) {
                        $error = true;
                    }
                } else {
                    $error = true;
                }

                if ($error) {
                    throw new Exception(sprintf('Incorrectly formatted array value for key "%s": "%s"', $matches[2], $matches[4]));
                }
            }

            $keyVal = new static(
                $matches[2],
                $value,
                $matches[1]
            );

            // TODO: check for comment.
            /*if (strpos($matches[7], '#')) {
                $keyVal->setComment(Comment::fromPlainString($tomlString));
            }*/

            return $keyVal;
        }

        throw new Exception(sprintf('Incorrectly formatted array string: "%s"', $tomlString));
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
            json_encode($this->value) .
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
