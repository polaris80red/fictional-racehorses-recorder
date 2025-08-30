<?php
class ThemeLoader {
    private string $themeBaseDirPath;
    public function __construct(string $themeBaseDirPath) {
        $this->themeBaseDirPath=$themeBaseDirPath;
    }
    public function loadTheme($themeName) {
        $themeDirPath = "{$this->themeBaseDirPath}/{$themeName}";
        $configPath = "{$themeDirPath}/config.php";
        
        if (!file_exists($configPath)) {
            throw new Exception("設定ファイルが見つかりません: {$configPath}");
        }
        require($configPath);
        
        if (!isset($cfg)) {
            throw new Exception('設定ファイルに$cfg配列がありません');
        }
        if (!is_array($cfg)) {
            throw new Exception("設定内容が配列になっていません");
        }
        return new Theme($themeName, $themeDirPath, $cfg);
    }
}
