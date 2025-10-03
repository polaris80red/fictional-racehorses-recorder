fictional-racehorses-recorder 架空競走馬戦績管理ツール
====

競馬創作や競走馬擬人化創作を執筆する際のために、競走成績や対戦関係を競馬情報サイトのようなデータベースで管理できるWEBアプリケーションです。
- [Github](https://github.com/polaris80red/fictional-racehorses-recorder)

※ アップデート時は **_setup** ディレクトリの **UPDATE.txt** を参照してください

# 目次

- [必要環境](#必要環境)
- [導入手順-1](#導入手順1)
- [導入手順-2](#導入手順2)
- [導入手順-3](#導入手順3)
- [導入手順-4](#導入手順4)
- [製作者](#製作者)
- [ライセンス](#ライセンス)

## 必要環境
このツールは Apache + PHP + MySQL(MariaDB) 環境で動作するWEBアプリケーションです。
- XAMPPやLaragonなどを用いたローカル環境での利用を主に想定しています。

## 導入手順1
- 設置するWEBサイト領域の任意のディレクトリにファイルを展開してください。
- config.sample.inc.phpが入っているディレクトリがアプリのトップページのディレクトリです。
- **config.sample.inc.php** をコピーして設定ファイル **config.inc.php** を作成してください。

### ● config.inc.php｜必須の設定項目
データベース接続情報 **DB_HOST**、**DB_NAME**、**DB_USER**、**DB_PASS** は、使用するデータベースに合わせて値を変更してください。

### ● config.inc.php｜localhost以外で実行する場合のオプション設定
初期設定ではXAMPP等のlocalhost環境を想定しています。利用するコンピューター上以外で実行する場合は、リモートIPアドレスでのアクセス等を許可するように設定を変更してください。

**ALLOW_REMOTE_ACCESS**：
リモートIPアドレスでのアクセスの許可
- デフォルトでは拒否(false)としています。
- リモートIPアドレスでのアクセスを許可する場合はtrueに変更してください。

**ALLOW_REMOTE_EDITOR_LOGIN**：
リモートIPアドレスでのログインの許可
- デフォルトでは拒否(false)としています。
- リモートIPアドレスでのログインを許可する場合はtrueに変更してください。

**READONLY_MODE**：
閲覧専用モード
- trueに変更することで、IPアドレスに関係なくログイン禁止になります。 

**ADMINISTRATOR_USER**：
管理者ユーザーID
- 管理者ログイン用のIDです。WEBに公開する場合はadminからの変更を推奨します。

**ADMINISTRATOR_PASS**：
管理者パスワード
- 管理者ログイン用のパスワードを、平文ではなくpassword_hash関数で処理したハッシュ値で記載します。
- ※ALLOW_REMOTE_EDITOR_LOGINが有効な場合、パスワード無しのままではログインできません。

その他の設定はconfig.inc.php（config.sample.inc.php）の内容を確認してください。

## 導入手順2
### 外部Javascriptファイルの導入
基本的なJQuery関連のファイルはGoogleのCDNを利用していますが、カレンダー入力の動作には**datepicker-ja.js**を自分で設置する必要があります。
- https://github.com/jquery/jquery-ui/tree/main/ui/i18n
- 上記から「datepicker-ja.js」をダウンロードし、vendorディレクトリに配置してください。

## 導入手順3
### データベースの初期テーブルの作成
使用するデータベースに対して、 **_setup/sql/** 以下のディレクトリに同梱している.sqlファイルをインポートしてください。

**テーブルの構築**：
最初に **_setup/sql/create_tables/** ディレクトリ内の21個の.sqlファイルをインポートしてください。必須のテーブルを空で作成します。

**基本的なマスタデータの導入**：
続いて **_setup/sql/data/** ディレクトリ内の.sqlファイルをインポートしてください。基本的なマスタデータを登録します。
- **mst_affiliation.sql**：所属マスタ
- **mst_affiliation__optional_nar.sql**：(オプション)所属マスタの各地方競馬
    - [地方競馬 データ情報](https://www.keiba.go.jp/KeibaWeb/DataRoom/DataRoomTop) の所属場プルダウン順）
- **mst_race_category_age.sql**：レース年齢条件マスタ(必須)
- **mst_race_category_sex.sql**：レース性別条件マスタ(必須)
- **mst_race_course.sql**：レース場マスタ(推奨)
- **mst_race_course__optuonal_nar.sql**：(オプション)レース場マスタの地方競馬場
    - [月別開催日程｜地方競馬情報サイト](https://www.keiba.go.jp/KeibaWeb/MonthlyConveneInfo/MonthlyConveneInfoTop) の地方競馬サイトの開催日程表の順
- **mst_race_course__optuonal_other.sql**：(オプション)レース場マスタの一部国名
    - [海外競馬の勝馬投票券（馬券）発売のルール-JRA](https://www.jra.go.jp/keiba/overseas/rule/) の国コード順に一部の国名
- **mst_race_grade.sql**：レース格付マスタ(推奨)
- **mst_race_special_results.sql**：レース特殊結果マスタ(推奨)
- **mst_race_week.sql**：レース週マスタ(必須)
- **mst_themes.sql**：テーママスタ

## 導入手順4
競走馬・レースデータの登録には、ワールドを作成・選択しておく必要があります。

### ワールド設定の作成
レースや競走馬のデータはワールドに所属し、検索などでは選択中のワールドの項目だけに絞り込まれます。
- ログインして**管理画面**からワールド設定を開き、**ワールド新規登録**からワールドを作成します。
- **名称**は必須項目です。また、自動ID接頭語を設定しておくと、ワールド単位で戦績をエクスポートして移転したい場合などに検索条件に使えるので便利です。
- ワールドを新規登録した後は、そのワールドを選択した状態でストーリー設定の登録画面に移動します。

### ストーリー設定の作成
- プルダウンの選択起点になる年度やプルダウンの範囲などの表示設定が可能です。
- 「登録完了時にデフォルト設定を以下の設定で上書きする」もオンにしておくと、登録完了時点で、ログインしていないときにも適用するシステム全体の初期設定に、作成したストーリー設定を指定します。

# 製作者
author: **polaris80red**
- GitHub: [polaris80red](https://github.com/polaris80red)
- X (Twitter): [@PolestarRed](https://twitter.com/PolestarRed)
- note.com: [polaris8](https://note.com/polaris8)

# LICENSE
(ja) このプロジェクトは MIT License の元公開しています。 LICENSE.txt をご覧ください。

(en) This project is released under the MIT License, see LICENSE.txt file.