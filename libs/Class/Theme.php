<?php
class Theme {
    private array $config;
    private string $name;
    private string $dirPath;

    public function __construct(string $name, string $dirPath, array $config) {
        $this->name = $name;
        $this->dirPath = $dirPath;
        $this->config = $config;
    }
    public function getName(): string {
        return $this->name;
    }
    public function getDirPath(): string {
        return $this->dirPath;
    }
    public function getCssFiles(): array {
        return $this->config['css_files'] ?? [];
    }
}
