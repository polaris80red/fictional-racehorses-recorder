アップデート作業情報

# 【アップデートで上書きしないフォルダについて】
Windows環境では空フォルダで上書きしても既存ファイルは残りますが、ほかの環境では下記を上書きしないよう注意してください。

- userフォルダ：自身でカスタマイズしたcssやテンプレート等の配置先。
- storageフォルダ：設定ファイルやログファイルの書き込み先などの配置先。
- themesフォルダ：初期同梱のテーマはアップデートが必要なる場合があります。非Windows環境で自作テーマがある場合はthemesフォルダ自体を上書きせず、初期同梱テーマを個別に上書きしてください。
- vendorフォルダ：手動で導入している datepicker-ja.js 等の配置先のため。


# 【データ構造の変更等を伴うバージョンアップの履歴・必要手順】
## ■ v0.1.8 以下のバージョンからアップデートする場合
- データベースに mst_users および sys_login_attempt_ip テーブルが存在しない場合、v0.1.8 以下のバージョンです。
- _setup/sql/create_tables/の下記２つの.sqlを実行してテーブルを作成してください。
  - mst_users.sql
  - sys_login_attempt_ip.sql

## ■ v0.3.0 未満のバージョンからアップデートする場合
- データベースの dat_race_results テーブルに jra_thisweek_horse_26_title および jra_thisweek_horse_26_1 カラムがない場合、v0.3.0未満のバージョンです。
- _setup/sql/update/の from_before_v_0_3_0__dat_race_results.sql を実行してください。
