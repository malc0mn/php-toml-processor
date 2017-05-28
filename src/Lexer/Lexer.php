<?php

namespace Toml\Lexer;

use Toml\Exception;

class Lexer extends AbstractLexer
{
    const T_COMMENT = 'comment';
    const T_TABLE = 'table';
    const T_ARRAY_TABLE = 'array_table';
    const T_INLINE_TABLE = 'inline_table';
    const T_ARRAY = 'array';
    const T_ARRAY_END = 'array_end';
    const T_EMPTY_LINE = 'empty_line';
    const T_KEY_VALUE = 'key_value';
    const T_BARE_KEY_VALUE = 'bare_key_value';
    const T_STRING_MULTILINE_START = "string_multiline_start";
    const T_STRING_MULTILINE_END = "string_multiline_end";

    /**
     * Lexer constructor.
     *
     * @param string $input
     */
    public function __construct($input)
    {
        $this->setInput($input);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCatchablePatterns()
    {
        return [
            // Comments start with a hash (#) char.
            '#[^\n]*\n',
            // Quoted keys that start with a single (') or double (") quote
            // char. followed by an equals sign optionally surrounded by tabs
            // or spaces.
            '["\'].*["\'][\t ]*=[\t ]*[^\n]+\n',
            // Bare keys: NO quotes!
            '[A-Za-z0-9_\-]+[\t ]*=[\t ]*[^\n]+\n',
            // Tables.
            '\[[^\n]+]\n',
            // End of array.
            '\]\n',
            // Multiline end.
            '(?:"""|\'\'\')'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getNonCatchablePatterns()
    {
        return [
            // Do not match single chars.
            '.',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getType(&$value)
    {
        switch (true) {
            case strpos($value, '#') === 0:
                return self::T_COMMENT;

            // Keep this one...
            case strpos($value, '[[') === 0:
                return self::T_ARRAY_TABLE;
            // BEFORE this one!
            case strpos($value, '[') === 0:
                return self::T_TABLE;

            case preg_match('/^\n+/', $value):
                return self::T_EMPTY_LINE;

            case strpos($value, ' = {'):
                return self::T_INLINE_TABLE;

            // Keep these two...
            case strpos($value, ' = ['):
                return self::T_ARRAY;
            case strpos($value, ' = """') !== false ||
                strpos($value, " = '''") !== false:
                return self::T_STRING_MULTILINE_START;
            // Before these two...
            case strpos($value, ' = ') &&
                (strpos($value, '"') === 0 ||
                    strpos($value, "'") === 0):
                return self::T_KEY_VALUE;
            case strpos($value, ' = '):
                return self::T_BARE_KEY_VALUE;

            case strpos($value, ']') === 0:
                return self::T_ARRAY_END;

            case $value === '"""' || $value === "'''":
                return self::T_STRING_MULTILINE_END;
        }

        throw new Exception(sprintf('Unknown token "%s"', $value));
    }

    /**
     * TOML is case sensitive!!
     *
     * @return string
     */
    protected function getModifiers()
    {
        return '';
    }
}
