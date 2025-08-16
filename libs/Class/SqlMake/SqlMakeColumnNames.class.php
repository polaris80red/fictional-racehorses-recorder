<?php
class SqlMakeColumnNames{
    private array $columns;
    private string $table_name='';
    public function __construct(array $columns,string $table_name='') {
        $this->columns = $columns;
        $this->table_name = $table_name;
    }
    public function quoted(): array{
        return SqlMake::quoteColumns($this->columns,$this->table_name);
    }
    public function placeholders(): array {
        return SqlMake::placeholders($this->columns);
    }
    public function updateSet(): array {
        return SqlMake::updateSetClauses($this->columns,$this->table_name);
    }
    public function quotedString(string $glue = ', '): string {
        return implode($glue,$this->quoted());
    }
    public function placeholdersString(string $glue = ', '): string {
        return implode($glue,$this->placeholders());
    }
    public function updateSetString(string $glue = ', '): string {
        return implode($glue,$this->updateSet());
    }
}