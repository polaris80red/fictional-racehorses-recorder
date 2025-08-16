ライブラリのバージョンを変更する場合や使用するCDNを変更する場合などは、
vendor_config.sample.inc.php をコピーして vendor_config.inc.php を作り、
該当するファイルパスやタグを書き換えてください。
vendor_config.inc.php が存在する場合、 vendor_config.sample.inc.php より優先して設定を適用します。

# ダウンロード・設置が必須のライブラリ
## datepicker
- 導入方法の参考： https://web.skipjack.tokyo/javascript/jquery_ui_calendar/
- 下記にある「datepicker-ja.js」をダウンロードし、vendorフォルダ内に配置してください
  - https://github.com/jquery/jquery-ui/tree/main/ui/i18n


# CDNから読み込める物のリンク
## Google（jQuery UIのCSSも）
- https://developers.google.com/speed/libraries/?hl=ja#jquery-ui
- プリセットとしてはこちらに掲載されているリンクタグを使用しています。

### Jquery公式（jQuery本体、jQuery UI）
- https://releases.jquery.com/

# CDNから使用可能なものをローカルで導入する場合
vendor_config.inc.php の USE_LOCAL_JQUERY_FILE の設定値を false から true に変更することで、
ページ上のタグのリンク先が vendor ディレクトリ以下に配置したファイルに変更されます。

## jQuery 本体
- jQuery公式サイト https://jquery.com/
- jQuery公式からダウンロード
- 「jquery-3.7.1.min.js」ファイルをvendorフォルダに配置してください。

## jQuery UI
- jQuery UI 公式サイト https://jqueryui.com/
- jQuery公式サイトの Quick Downloads でダウンロード
  - v1.14.1
- 解凍して「jquery-ui-1.14.1」フォルダ自体をvendorフォルダに配置してください。
- バージョンが変わる場合、このフォルダにある vendor_config.inc.php の Jquery_UI_DIR の記述「jquery-ui-1.14.1」をそれに合わせて書き換えてください。

