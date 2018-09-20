<?
session_start();
include_once("prog_id.php");
include_once("mod/common.php");

$errorlog=basename(__FILE__,".php").".log";

// �����擾
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

// ������ nickname �ɊG�����������Ă�������� 2013-06-14 ���ǉ� ������
// �� �G�����̍폜
// ���͕����G���R�[�f�B���O�������A�܂��͏o�͕����G���R�[�f�B���O�ɕ����R�[�h�����݂��Ȃ��ꍇ�̑�֕������w��(none:�o�͂��Ȃ�)
mb_substitute_character('none');
// ���ۂɕϊ����s��
$nickname=mb_convert_encoding($nickname, 'SJIS', 'SJIS');

// �� ��ŏ����Ȃ��\�t�g�o���N�G�����̏���
$pattern='/[\\x1B][\\x24][G|E|F|O|P|Q][\\x21-\\x7E]+([\\x0F]|$)/';
preg_match_all($pattern, $nickname, $arr);
// $arr[0] �ɑΏۊG�������i�[�����
$search=$arr[0];
// �G��������
$nickname=str_replace($search, array(), $nickname);
// ������

// ����
$prf_no01=cgi_get("prf_no01");


// �߂�l���擾[utl.com]	2009-10-09 ���ǉ�
$ret=tel_chk($tel);
if($ret<>0){
	if($ret==-4){
		$msg.="���̓d�b�ԍ��͓o�^�ł��܂���<br>";
	}
	else{
		$msg.="�d�b�ԍ����m�F���Ă�������<br>";
	}
}
// 2017-01-31 kawakami add
$new_tel_e=sql_enc_get($tel);
$db=sql_connect($prog_id);
$sqlcmd="SELECT COUNT(*) cnt FROM Member WHERE use_mode <> 1 AND tel='$new_tel_e'";
sql_count($db, $sqlcmd, $get_cnt);
if($get_cnt <> 0){

	$msg.="���̓d�b�ԍ��͓o�^�ς݂ł�<br>";

	make_error_log($errorlog, "tel:".$tel." sqlcmd".$sqlcmd);
}

if($nickname=="" || mb_strlen($nickname)>8){
	$msg.="�����́A8�����܂łł�<br>";
}
if(pass_chk($pass,0,4)<>0){
	$msg.="�߽ܰ�ނ͐����̂݁A4���ł�<br>";
}
if(!checkdate($birth_mm,$birth_dd,$birth_yyyy)){
	$msg.="�a�����̎w��Ɍ�肪����܂�<br>";
}
/*
else if(!(get_age($birth)>=18)){
	$msg.="18�Ζ����͌䗘�p�ł��܂���<br>";
}
*/
// 20�Ζ����t���O
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
	$rankname="�㕥��";
}
else{
	$rankname="�O����";
}

$prf_no01_str="";
if($prf_no01=='1'){
	$prf_no01_str="�j��";
}
else{
	$prf_no01_str="����";
}


ob_start();
include_once(get_tmpl("tmpl/m_regprfchk.html"));
$work=ob_get_clean();
$work=emoji_decode($work);
$work=mob_html_chg($work);
$work=chg_word_by_advid($work, $_SESSION['adv_id']);
print($work);
?>
