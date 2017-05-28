<?php

namespace Toml\Printables;

use Toml\Lexer\Lexer;

class Table extends Printable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Printable[]
     */
    protected $printables = [];

    /**
     * @var Table[]
     */
    protected $childTables = [];

    /**
     * @var Table
     */
    protected $parentTable;

    /**
     * Table constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $parts = explode('.', str_replace(['[', ']'], '', $name));
        $this->name = trim(end($parts));
    }

    /**
     * @param Lexer $tomlString
     * @param Table $parent
     *
     * @return static
     */
    public static function fromString(Lexer $tomlString, Table $parent = null) {
        static $recusrionBreak = false;

        // Get the name first.
        $table = new static($tomlString->token['value']);
        if ($parent === null) {
            $parent = $table;
        } else {
            $table->setParentTable($parent);
        }

        $break = false;
        while ($recusrionBreak === false && $break === false && $tomlString->moveNext()) {
            switch ($tomlString->token['type']) {
                case Lexer::T_COMMENT:
                    $table->addPrintable(
                        Comment::fromString($tomlString->token['value'])
                    );
                    break;

                case Lexer::T_EMPTY_LINE:
                    $table->addPrintable(
                        EmptyLine::fromString()
                    );
                    break;

                case Lexer::T_BARE_KEY_VALUE:
                    $table->addPrintable(
                        BareKeyValue::fromString($tomlString->token['value'])
                    );
                    break;

                case Lexer::T_KEY_VALUE:
                    $table->addPrintable(
                        KeyValue::fromString($tomlString->token['value'])
                    );
                    break;
                case Lexer::T_ARRAY:
                    $table->addPrintable(
                        Arrey::fromString($tomlString, $tomlString->token['value'])
                    );
                    break;
                case Lexer::T_TABLE:
                    if ($recusrionBreak === false) {
                        // Remember: the method we're in is being called recursively
                        // for nested table support. At some point we will encounter
                        // a table token that is no longer of interest so we need
                        // to break out of the recursion here!
                        $tomlString->movePrevious();
                        $recusrionBreak = true;
                    }
                    break;
            }

            // Now look ahead and see if we will encounter a new table.
            switch ($tomlString->lookahead['type']) {
                case Lexer::T_TABLE:
                    // Special case here: if this is a NEW table, we break the
                    // entire while loop else we add it as a child table.
                    if (
                        Table::isNestedTable($tomlString->lookahead['value']) &&
                        Table::isChildOf($tomlString->lookahead['value'], $parent->getName())
                    ) {
                        // Move to this table token and start the table process
                        // all over again.
                        $tomlString->moveNext();
                        $parent->addChildTable(
                            Table::fromString($tomlString, $parent)
                        );
                    } else {
                        $break = true;
                        break;
                    }
                    break;
            }
        }

        // Reset static variable so we can re-use this method for other tables.
        if ($recusrionBreak === true) {
            $recusrionBreak = false;
        }
        return $parent !== null ? $table : $parent;
    }

    public static function isNestedTable($tableName)
    {
        return count(explode('.', str_replace(['[', ']'], '', trim($tableName)))) > 1;
    }

    public static function isChildOf($tableName, $parentTableName)
    {
        if ($parentTableName === null) {
            return false;
        }
        // TODO: verify this function: I don't think all cases are covered!
        $parts = explode('.', str_replace(['[', ']'], '', trim($tableName)));
        $position = array_search(trim($parentTableName), $parts, true);

        // When the occurence of the parent table name is smaller than the last
        // element in the array, it the parent!
        return $position !== false && $position < count($parts) - 1;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFullName()
    {
        $name = $this->name;
        if ($this->parentTable !== null) {
            $name = $this->parentTable->getName() . '.' . $name;
        }
        return $name;
    }

    public function addChildTable(Table $table)
    {
        $this->childTables[$table->getName()] = $table;
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

    public function setParentTable(Table $table)
    {
        $this->parentTable = $table;
    }

    /**
     * {@inheritdoc}
     */
    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        $indent = str_repeat(str_repeat(' ', $spacesPerIndent), $indentLevel);

        // Print name.
        $resultString = $indent . '[' . $this->getFullName() . "]\n";

        // Print table elements.
        foreach ($this->printables as $printable) {
            $resultString .= $printable->prettyPrint($indentLevel, $spacesPerIndent);
        }

        // Print child tables.
        foreach ($this->childTables as $childTable) {
            $resultString .= $childTable->prettyPrint($indentLevel + 1, $spacesPerIndent);
        }

        return $resultString;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $data = [];
        foreach ($this->printables as $printable) {
            $arr = $printable->toArray();
            if ($arr !== null) {
                $data = array_merge($data, $arr);
            }
        }
        foreach ($this->childTables as $table) {
            $arr = $table->toArray();
            if ($arr !== null) {
                $data = array_merge($data, $arr);
            }
        }
        return [$this->getName() => $data];
    }
}
