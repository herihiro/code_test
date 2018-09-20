<?php
/*
Google �A�J�E���g�𗘗p���ă��A�h�Q�b�g(OAuth�F��)

�E�Q�l�T�C�g�Fhttp://takahitokikuchi.poitan.net/2011/08/11/google

�EGoogle API Console�ihttp://code.google.com/apis/console�j�� [API Access] �ŃA�v����o�^
	1. [Credentials] �� CLIENT ID ���쐬����
	2. [Consent screen] �� Email address �� Product name �����߂�
		Product name �͈�������߂��Ȃ������Ȃ̂ŁA�ԑg���ƂɃA�J�E���g���K�v����

�}�C�N���A�N�Z�X�����
�@����Google �A�J�E���g:078319@gmail.com
�@�p�X���[�h:57215721

�E�u���E�U���� $my_url �ɃA�N�Z�X�����
	1. ���O�C�����Ă��Ȃ��ꍇ�A���O�C���F�؃y�[�W��\���B
	2. ���Ƀ��O�C���ς݂̏ꍇ�A�쐬�����A�v�����A�N�Z�X������e��\���B
	  �u�A�N�Z�X���v��I������ƔF�؁i$code ���t�����ċA���Ă���j�B
�@�@�@ ���łɔF�؂��ꂽ�T�[�r�X���폜����ꍇ�ɂ� Google[�A�J�E���g���]��[�A�v���P�[�V�����ƃT�C�g��F��]��"�A�N�Z�X��������"�B
*/

include_once("prog_id.php");
include_once("mod/common.php");

///// �����ݒ� /////
$app_id		="769819242225.apps.googleusercontent.com";	// Client ID
$app_secret	="RqUXmBC0v-ShUCXcVJtXP_Du";				// Client secret
$my_url		="http://078319.jp/prog10_i/gg_login.php";	// Redirect URIs
///// �����ݒ� /////

$code = $_REQUEST["code"];

if(empty($code))
{
	$dialog_url = "https://accounts.google.com/o/oauth2/auth?";
	$dialog_url .= "scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile&";
	$dialog_url .= "client_id=" . $app_id . "&redirect_uri=" . urlencode($my_url) . "&response_type=code";

	echo("<script> top.location.href='" . $dialog_url . "'</script>");
}

// AuthZ Server��Request�𑗐M(Access Token�擾)
$token_url = "https://accounts.google.com/o/oauth2/token";
$params = "code=" . $code;
$params .= "&client_id=" . $app_id;
$params .= "&client_secret=" . $app_secret;
$params .= "&redirect_uri=" . urlencode($my_url);
$params .= "&grant_type=authorization_code";

$response = dorequest($token_url, $params, 'POST');
$response = json_decode($response);

// �ϐ����Z�b�g����Ă��邩
if(isset($response->access_token))
{
	// UserInfo Endpoint�ɃA�N�Z�X
	$info_url = 'https://www.googleapis.com/oauth2/v1/userinfo';
	$params = 'access_token=' . urlencode($response->access_token);

	unset($response);

	$response = dorequest($info_url, $params, 'GET');

	/*
	$response �̕�����̗�
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

	// email ���擾
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


/////������ ���̃T�[�o�ƒʐM����֐� ������/////
/*
php �� --with-curl �ŃR���p�C���̂���

CURLOPT_URL			�F�擾����URL
CURLOPT_POSTFIELDS	�FHTTP POST���M����f�[�^
CURLOPT_POST		�FTRUE���w�肷��ƁAapplication/x-www-form-urlencoded�`����HTTP POST���M���s��
CURLOPT_RETURNTRANSFER�FTRUE���w�肷��ƁAcurl_exec()�̕Ԃ�l�𕶎���ŕԂ��B�ʏ�̓f�[�^�����ڏo�͂����
*/
function dorequest($url, $params, $type)
{
	// cURL �Z�b�V������������
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

	// �ϐ���j��
	unset($response);

	// URL ���擾���ău���E�U�ɓn��
	$response = curl_exec($ch);

	// cURL ���\�[�X����ăV�X�e�����\�[�X�����
	curl_close($ch);

	return $response;
}
/////������
?>