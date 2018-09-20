<?php
/*
Google アカウントを利用してメアドゲット(OAuth認証)

・参考サイト：http://takahitokikuchi.poitan.net/2011/08/11/google

・Google API Console（http://code.google.com/apis/console）の [API Access] でアプリを登録
	1. [Credentials] で CLIENT ID を作成する
	2. [Consent screen] で Email address と Product name を決める
		Product name は一つしか決められなさそうなので、番組ごとにアカウントが必要かも

マイクロアクセスから提供
　弊社Google アカウント:078319@gmail.com
　パスワード:57215721

・ブラウザから $my_url にアクセスすると
	1. ログインしていない場合、ログイン認証ページを表示。
	2. 既にログイン済みの場合、作成したアプリがアクセスする内容を表示。
	  「アクセス許可」を選択すると認証（$code が付属して帰ってくる）。
　　　 すでに認証されたサービスを削除する場合には Google[アカウント情報]の[アプリケーションとサイトを認証]で"アクセスを取り消す"。
*/

include_once("prog_id.php");
include_once("mod/common.php");

///// 初期設定 /////
$app_id		="769819242225.apps.googleusercontent.com";	// Client ID
$app_secret	="RqUXmBC0v-ShUCXcVJtXP_Du";				// Client secret
$my_url		="http://078319.jp/prog10_i/gg_login.php";	// Redirect URIs
///// 初期設定 /////

$code = $_REQUEST["code"];

if(empty($code))
{
	$dialog_url = "https://accounts.google.com/o/oauth2/auth?";
	$dialog_url .= "scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile&";
	$dialog_url .= "client_id=" . $app_id . "&redirect_uri=" . urlencode($my_url) . "&response_type=code";

	echo("<script> top.location.href='" . $dialog_url . "'</script>");
}

// AuthZ ServerにRequestを送信(Access Token取得)
$token_url = "https://accounts.google.com/o/oauth2/token";
$params = "code=" . $code;
$params .= "&client_id=" . $app_id;
$params .= "&client_secret=" . $app_secret;
$params .= "&redirect_uri=" . urlencode($my_url);
$params .= "&grant_type=authorization_code";

$response = dorequest($token_url, $params, 'POST');
$response = json_decode($response);

// 変数がセットされているか
if(isset($response->access_token))
{
	// UserInfo Endpointにアクセス
	$info_url = 'https://www.googleapis.com/oauth2/v1/userinfo';
	$params = 'access_token=' . urlencode($response->access_token);

	unset($response);

	$response = dorequest($info_url, $params, 'GET');

	/*
	$response の文字列の例
	{
	"id": "107068623649770000581",
	"email": "herihiro@gmail.com",
	"verified_email": true,
	"name": "Nori Kawa",
	"given_name": "Nori",
	"family_name": "Kawa",
	"link": "https://plus.google.com/107068623649770000581",
	"gender": "male",
	"locale": "ja",
	}
	*/
	$data = explode("," , $response);

	// email を取得
	for($i=0; $i<count($data); $i++){

		if(preg_match('/\"email\": /', $data[$i])){

			$data_email=$data[$i];

			break;
		}
	}
	$data_email	=str_replace('"', '', $data_email);
	$pieces		=explode(":", $data_email);
	$email		=trim($pieces[1]);

	header("Location:oauth_member.php?email=".urlencode($email)."");
}


/////■■■ 他のサーバと通信する関数 ■■■/////
/*
php を --with-curl でコンパイルのこと

CURLOPT_URL			：取得するURL
CURLOPT_POSTFIELDS	：HTTP POST送信するデータ
CURLOPT_POST		：TRUEを指定すると、application/x-www-form-urlencoded形式でHTTP POST送信を行う
CURLOPT_RETURNTRANSFER：TRUEを指定すると、curl_exec()の返り値を文字列で返す。通常はデータが直接出力される
*/
function dorequest($url, $params, $type)
{
	// cURL セッションを初期化
	$ch = curl_init();

	if($type == 'POST')
	{
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_POST, 1);
	}
	else{
		curl_setopt($ch, CURLOPT_URL, $url . "?" . $params);
	}

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	// 変数を破棄
	unset($response);

	// URL を取得してブラウザに渡す
	$response = curl_exec($ch);

	// cURL リソースを閉じてシステムリソースを解放
	curl_close($ch);

	return $response;
}
/////■■■
?>