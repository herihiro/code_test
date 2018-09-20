<?
session_start();
include_once("prog_id.php");
include_once("mod/common.php");
include_once("mod/tran_sche.php");

aaaaaaaaaaaaaaaaaaaaaa

$errorlog=basename(__FILE__,".php").".log";

$main_tab=cgi_get("main_tab");
$sub_tab =cgi_get("sub_tab");

$past_flag=cgi_get("past_flag");

$sid=get_session("sid");
if($sid==""){
	header("Location:index.php");
	exit();
}
$prog_id=cgi_get("prog_id");
if($prog_id==""){
	header("Location:index.php");
	exit();
}
$prog_id_sch=cgi_get("prog_id_sch");


$prog_name=$def_prog_name[$prog_id];
if($prog_name==""){
	header("Location:index.php");
	exit();
}

// 表示
$db_com=sql_connect($common_db);
$sqlcmd ="SELECT * FROM TranMachiSche";
$sqlcmd.=" WHERE id > 0";
$sqlcmd.=" AND del_flg=0";
$sqlcmd.=" ORDER BY char_id2 ASC, ctime ASC";
$rs=sql_cmd($db_com, $sqlcmd);

// 順番を移動させるリンクに付加するURL
$add_move_url ="?prog_id=".$prog_id;
$add_move_url.="&main_tab=".$main_tab;
$add_move_url.="&sub_tab=".$sub_tab;

// 同じ鑑定士内での順番
$char_id2_cnt="1";

while($obj=sql_fetch($rs)){

	$obj->profile_info_teller=profile_info_get($prog_id, $obj->char_id2);

	$obj->profile_info_user=profile_info_get($prog_id, $obj->char_id);

	///// 状態 /////
	// 状態の判定
	if($obj->talk_end_time <> '') $obj->kind_str="予約鑑定済";
	elseif($obj->send_time <> "" && $obj->timeover_flg=='1') $obj->kind_str="鑑定来ず";
	elseif($obj->wait_level == '91') $obj->kind_str="ユーザーキャンセル";
	elseif($obj->wait_level == '92') $obj->kind_str="鑑定師キャンセル";
	elseif($obj->wait_level == '93') $obj->kind_str="管理キャンセル";
	elseif($obj->wait_level == '94') $obj->kind_str="待機終了";
	elseif($obj->send_time <> "") $obj->kind_str="メール送信済み";

	// 会話中
	$set_idcode2=substr($obj->char_id2, 0, strlen($obj->char_id2) - 3);
	$set_idcode =substr($obj->char_id, 0, strlen($obj->char_id) - 3);

	$db_com =sql_connect($common_db);
	$sqlcmd ="SELECT * FROM Talking WHERE prog_id='".$ope_id."'";
	$sqlcmd.=" AND idcode='".$set_idcode2."'";
	$sqlcmd.=" AND idcode2='".$set_idcode."'";
	$sqlcmd.=" ORDER BY prog_id DESC";
	$talking_info=sql_one($db_com, $sqlcmd);
	if($talking_info) $obj->kind_str="鑑定中";
	/////

	///// 順番を移動させるリンク /////
	if($obj->char_id2 <> $old_char_id2) $char_id2_cnt="1";

	if(!($obj->char_id=="000000001") && !$obj->kind_str){

		$obj->move_str ="<a href=\"tran_machireserve_list_move.php".$add_move_url."&move_flag=1&tts_id=".$obj->id."\"><img src=\"images/yaji_red.png\" width=\"18\"></a>";
		$obj->move_str.="<a href=\"tran_machireserve_list_move.php".$add_move_url."&move_flag=2&tts_id=".$obj->id."\"><img src=\"images/yaji_blue.png\" width=\"18\"></a>";
	}

	$old_char_id2=$obj->char_id2;

	make_error_log($errorlog, "[cnt]:".$char_id2_cnt." [char_id2]:".$obj->char_id2);

	$char_id2_cnt++;
	/////

	$work_list[]=$obj;
}

$msg=cgi_get("msg");

header('Content-Type: text/html;charset=Shift-JIS');

ob_start();

// 自動更新の javascript フラグ
$js_flag='0';
$js_flag=cgi_get("js_flag");

if($js_flag=='1') $tmpl_name='tran_machireserve_list_right.html';
else $tmpl_name='tran_machireserve_list.html';

include_once("tmpl/".$tmpl_name);

$work=ob_get_clean();
print($work);
exit();
?>
