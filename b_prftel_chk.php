<?
session_start();
include_once("prog_id.php");
include_once("mod/common.php");

// 引数取得
$code=cgi_get("code");
$pass=cgi_get("pass");
$login_flag=cgi_get("login_flag");

// 全角数字だったら半角数字にする 2011-04-13 川上追加
$new_tel=cgi_get("new_tel");
$new_tel=mb_convert_kana($new_tel,n,"SJIS");;

$idcode2_e=cgi_get("idcode2_e");

/////////////////////////////////////////////
// ここからログイン処理
/////////////////////////////////////////////
if($login_flag==""){
	$sid=get_session("sid");
	if($sid==""){
		$msg="ログイン情報を確認できませんでした<br>再度ログインを行ってください";
		$msg=rawurlencode($msg);
		header("Location:index.php?msg=$msg");
		exit();
	}
}
else if($login_flag==1){
	if($pass==""){
		$msg="パスワードを入力してください";
		$msg=rawurlencode($msg);
		header("Location:b_login.php?msg=$msg&code=$code");
		exit();
	}
}

$rtn=member_login($prog_id,$code,$pass,$flag,&$sid);
if($rtn<>0){
	$msg="ログイン情報を確認できませんでした<br>再度ログインを行ってください";
	$msg=rawurlencode($msg);
	header("Location:index.php?msg=$msg&code=$code&pass=$pass");
	exit();
}
if($login_flag==1) set_session("sid",$sid);

// 会員情報を取得
$idcode=get_decode($sid);
$member_info=member_info_get($prog_id,$idcode);
if($member_info->use_mode>0){
	header("Location:member_stop.php?$ssname=$ssid");
	exit();
}

// プロフィール情報を取得
$profile_info_all=profile_info_all($prog_id,$member_info->memb_id);
if(count($profile_info_all)==1){
	$profile_info=$profile_info_all[0];
}
// キャラがない場合
else if(count($profile_info_all)==0){
}
// 複数キャラの場合
else{
}
/////////////////////////////////////////////
// ここまでログイン処理
/////////////////////////////////////////////

///// エラー処理 /////
if(tel_chk($new_tel)<>0){
	$errmsg.="電話番号を確認してください<br>";
}

$telb_ret=member_reg_telb_chktel($prog_id,$new_tel);
if($telb_ret==3){
	$errmsg.="現在、この電話番号の登録を保留させて頂いております。<br>不明な点は<a href=\"tel:".$toi_tel."\">お客様ｾﾝﾀｰ</a>までお問い合わせ下さい<br>";
}

if($errmsg<>""){
	header("Location:b_prftel.php?$ssname=$ssid&errmsg=$errmsg");
	exit();
}

// 電話番号チェック
$rtn=black_chk($new_tel,1);
if($rtn<>0){
	$errmsg="その電話番号は利用できません<br>";
}
else if($new_tel){
	
	$chg_flg='0';
	$chg_flg=cgi_get("chg_flg");
	// TEST
	if($chg_flg <> '1' && $member_info->staff_flg <> '1'){

		$new_tel_e=sql_enc_get($new_tel);
		$db=sql_connect($prog_id);
		$sqlcmd="SELECT COUNT(*) cnt FROM Member WHERE use_mode<>1 AND tel='$new_tel_e'";
		sql_count($db,$sqlcmd,$get_cnt);
		if($get_cnt<>0){
			$errmsg="その電話番号は登録済みです<br>";
		}
	}
}

if($errmsg<>""){
	header("Location:b_prftel.php?$ssname=$ssid&errmsg=$errmsg");
	exit();
}
///// エラー処理 /////

// 電話番号(予約)情報の更新
$up_flag->tel_reserve=1;

$up_data->memb_id=$member_info->memb_id;
$up_data->tel_reserve=$new_tel;
member_chg($prog_id,$up_data,$up_flag,$member_info);

// 電話番号が変わったのでメンバー情報を再取得
$member_info=member_info_get($prog_id,$idcode);

/////////////////////////////////////////////
// CTIにコマンドを投げる
/////////////////////////////////////////////
$cti_cmd_info->cti_cmd="TelChg";

$user_info->prog_id=$prog_id;
$user_info->member_info=$member_info;
$user_info->profile_info=$profile_info;

cti_command($cti_cmd_info,$user_info,$user_info2);
/////////////////////////////////////////////
// CTIにコマンドを投げる
/////////////////////////////////////////////


ob_start();
include_once(get_tmpl("tmpl/b_prftel_chk.html"));
$work=ob_get_clean();
$work=emoji_decode($work);
$work=mob_html_chg($work);
$work=chg_word_by_advid($work, $_SESSION['adv_id']);
print($work);
?>
