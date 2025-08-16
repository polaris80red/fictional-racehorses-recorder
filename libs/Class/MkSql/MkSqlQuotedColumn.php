<?php
class MkSqlQuotedColumn extends MkSqlQuotedIdentifier
{
    /**
     * ASを追加
     */
    public function As(string $alias): string
    {
        $aliasEscaped = str_replace('`', '``', $alias);
        return "`{$this->name}` AS `{$aliasEscaped}`";
    }
}
