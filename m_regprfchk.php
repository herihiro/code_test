<?
session_start();
include_once("prog_id.php");
include_once("mod/common.php");

$errorlog=basename(__FILE__,".php").".log";

// 引数取得
$mail=cgi_get("mail");
$adv_id=cgi_get("adv_id");
$p2=cgi_get("p2");

if($mail==""){
	header("Location:not_regist.php");
	exit();
}

$rank=cgi_get("rank");
$rank_select[$rank]=" selected";

$nickname	=cgi_get("nickname");
$pass		=cgi_get("pass");
$tel		=cgi_get("tel");

$birth_yyyy	=cgi_get("birth_yyyy");
$birth_mm	=cgi_get("birth_mm");
$birth_dd	=cgi_get("birth_dd");
$birth		=$birth_yyyy."-".$birth_mm."-".$birth_dd;

// ■■■ nickname に絵文字が入っていたら消す 2013-06-14 川上追加 ■■■
// ■ 絵文字の削除
// 入力文字エンコーディングが無効、または出力文字エンコーディングに文字コードが存在しない場合の代替文字を指定(none:出力しない)
mb_substitute_character('none');
// 実際に変換を行う
$nickname=mb_convert_encoding($nickname, 'SJIS', 'SJIS');

// ■ 上で消せないソフトバンク絵文字の除去
$pattern='/[\\x1B][\\x24][G|E|F|O|P|Q][\\x21-\\x7E]+([\\x0F]|$)/';
preg_match_all($pattern, $nickname, $arr);
// $arr[0] に対象絵文字が格納される
$search=$arr[0];
// 絵文字除去
$nickname=str_replace($search, array(), $nickname);
// ■■■

// 性別
$prf_no01=cgi_get("prf_no01");


// 戻り値を取得[utl.com]	2009-10-09 川上追加
$ret=tel_chk($tel);
if($ret<>0){
	if($ret==-4){
		$msg.="この電話番号は登録できません<br>";
	}
	else{
		$msg.="電話番号を確認してください<br>";
	}
}
// 2017-01-31 kawakami add
$new_tel_e=sql_enc_get($tel);
$db=sql_connect($prog_id);
$sqlcmd="SELECT COUNT(*) cnt FROM Member WHERE use_mode <> 1 AND tel='$new_tel_e'";
sql_count($db, $sqlcmd, $get_cnt);
if($get_cnt <> 0){

	$msg.="その電話番号は登録済みです<br>";

	make_error_log($errorlog, "tel:".$tel." sqlcmd".$sqlcmd);
}

if($nickname=="" || mb_strlen($nickname)>8){
	$msg.="姓名は、8文字までです<br>";
}
if(pass_chk($pass,0,4)<>0){
	$msg.="ﾊﾟｽﾜｰﾄﾞは数字のみ、4桁です<br>";
}
if(!checkdate($birth_mm,$birth_dd,$birth_yyyy)){
	$msg.="誕生日の指定に誤りがあります<br>";
}
/*
else if(!(get_age($birth)>=18)){
	$msg.="18歳未満は御利用できません<br>";
}
*/
// 20歳未満フラグ
$under20_flag='0';
if(!(get_age($birth_yyyy."-".$birth_mm."-".$birth_dd) >= 20)){

	$under20_flag='1';
}
set_session("under20_flag", $under20_flag);

if($msg<>""){
	ob_start();
	include_once(get_tmpl("tmpl/m_regprf.html"));
	$work=ob_get_clean();
	$work=emoji_decode($work);
	$work=mob_html_chg($work);
	print($work);
	exit();
}

$rankname="";
if($rank==1){
	$rankname="後払い";
}
else{
	$rankname="前払い";
}

$prf_no01_str="";
if($prf_no01=='1'){
	$prf_no01_str="男性";
}
else{
	$prf_no01_str="女性";
}


ob_start();
include_once(get_tmpl("tmpl/m_regprfchk.html"));
$work=ob_get_clean();
$work=emoji_decode($work);
$work=mob_html_chg($work);
$work=chg_word_by_advid($work, $_SESSION['adv_id']);
print($work);
?>
