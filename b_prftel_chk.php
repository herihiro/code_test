<?
session_start();
include_once("prog_id.php");
include_once("mod/common.php");

// �����擾
$code=cgi_get("code");
$pass=cgi_get("pass");
$login_flag=cgi_get("login_flag");

// �S�p�����������甼�p�����ɂ��� 2011-04-13 ���ǉ�
$new_tel=cgi_get("new_tel");
$new_tel=mb_convert_kana($new_tel,n,"SJIS");;

$idcode2_e=cgi_get("idcode2_e");

/////////////////////////////////////////////
// �������烍�O�C������
/////////////////////////////////////////////
if($login_flag==""){
	$sid=get_session("sid");
	if($sid==""){
		$msg="���O�C�������m�F�ł��܂���ł���<br>�ēx���O�C�����s���Ă�������";
		$msg=rawurlencode($msg);
		header("Location:index.php?msg=$msg");
		exit();
	}
}
else if($login_flag==1){
	if($pass==""){
		$msg="�p�X���[�h����͂��Ă�������";
		$msg=rawurlencode($msg);
		header("Location:b_login.php?msg=$msg&code=$code");
		exit();
	}
}

$rtn=member_login($prog_id,$code,$pass,$flag,&$sid);
if($rtn<>0){
	$msg="���O�C�������m�F�ł��܂���ł���<br>�ēx���O�C�����s���Ă�������";
	$msg=rawurlencode($msg);
	header("Location:index.php?msg=$msg&code=$code&pass=$pass");
	exit();
}
if($login_flag==1) set_session("sid",$sid);

// ��������擾
$idcode=get_decode($sid);
$member_info=member_info_get($prog_id,$idcode);
if($member_info->use_mode>0){
	header("Location:member_stop.php?$ssname=$ssid");
	exit();
}

// �v���t�B�[�������擾
$profile_info_all=profile_info_all($prog_id,$member_info->memb_id);
if(count($profile_info_all)==1){
	$profile_info=$profile_info_all[0];
}
// �L�������Ȃ��ꍇ
else if(count($profile_info_all)==0){
}
// �����L�����̏ꍇ
else{
}
/////////////////////////////////////////////
// �����܂Ń��O�C������
/////////////////////////////////////////////

///// �G���[���� /////
if(tel_chk($new_tel)<>0){
	$errmsg.="�d�b�ԍ����m�F���Ă�������<br>";
}

$telb_ret=member_reg_telb_chktel($prog_id,$new_tel);
if($telb_ret==3){
	$errmsg.="���݁A���̓d�b�ԍ��̓o�^��ۗ������Ē����Ă���܂��B<br>�s���ȓ_��<a href=\"tel:".$toi_tel."\">���q�l����</a>�܂ł��₢���킹������<br>";
}

if($errmsg<>""){
	header("Location:b_prftel.php?$ssname=$ssid&errmsg=$errmsg");
	exit();
}

// �d�b�ԍ��`�F�b�N
$rtn=black_chk($new_tel,1);
if($rtn<>0){
	$errmsg="���̓d�b�ԍ��͗��p�ł��܂���<br>";
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
			$errmsg="���̓d�b�ԍ��͓o�^�ς݂ł�<br>";
		}
	}
}

if($errmsg<>""){
	header("Location:b_prftel.php?$ssname=$ssid&errmsg=$errmsg");
	exit();
}
///// �G���[���� /////

// �d�b�ԍ�(�\��)���̍X�V
$up_flag->tel_reserve=1;

$up_data->memb_id=$member_info->memb_id;
$up_data->tel_reserve=$new_tel;
member_chg($prog_id,$up_data,$up_flag,$member_info);

// �d�b�ԍ����ς�����̂Ń����o�[�����Ď擾
$member_info=member_info_get($prog_id,$idcode);

/////////////////////////////////////////////
// CTI�ɃR�}���h�𓊂���
/////////////////////////////////////////////
$cti_cmd_info->cti_cmd="TelChg";

$user_info->prog_id=$prog_id;
$user_info->member_info=$member_info;
$user_info->profile_info=$profile_info;

cti_command($cti_cmd_info,$user_info,$user_info2);
/////////////////////////////////////////////
// CTI�ɃR�}���h�𓊂���
/////////////////////////////////////////////


ob_start();
include_once(get_tmpl("tmpl/b_prftel_chk.html"));
$work=ob_get_clean();
$work=emoji_decode($work);
$work=mob_html_chg($work);
$work=chg_word_by_advid($work, $_SESSION['adv_id']);
print($work);
?>
