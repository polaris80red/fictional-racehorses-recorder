<?php
class SqlValueNormalizer {
    /**
     * LIKE検索のエスケープ
     */
    public static function escapeLike($value) {
        $escape_char='\\';
        return str_replace(
            [$escape_char, '%', '_'],
            [$escape_char.$escape_char, $escape_char.'%', $escape_char.'_'],
            (string)$value
        );
    }
    /**
     * INT型で0を使うDEFAULT NULLカラム：空文字列とnullをnullに揃える（0はそのまま）
     */
    public static function nullIfEmpty($value) {
        $value=(string)$value;
        return $value === '' ? null : (int)$value;
    }
    /**
     * INT型で0を使わないDEFAULT NULLカラム：0はnullに揃える
     */
    public static function nullIfZero($value) {
        $value=(int)$value;
        return $value === 0 ? null : $value;
    }
    // INT型NOT NULLカラム：(int)で変換
    // 文字列型NOT NULLカラム：(string)で変換
}
