<?php
abstract class MkSqlQuotedIdentifier {
    protected string $name;

    public function __construct(string $name)
    {
        // バッククォートを二重化してエスケープ
        $this->name = str_replace('`', '``', $name);
    }

    public function __toString(): string
    {
        return "`{$this->name}`";
    }
}
