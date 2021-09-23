<?php

function genqr($username, $password) {
	$output = shell_exec('su - $username | google-authenticator -d -t -f --window-size=3 --rate-limit=3 --rate-time=30');

	echo "<br>";
	echo '<div>RAW:</div>';
	echo "<br>";
	echo '<pre>';
	echo $output;
	echo '</pre>';

	//QR code
	echo "<br>";
	echo '<div>QRCODE:</div>';
	echo "<br>";
	$data = 'otpauth://totp/apache@cpc-vpn-radius%3Fsecret%3DOQOZRAID2FEX3L7YHLAOGYILPI%26issuer%3Dcpc-vpn-radius';
	echo '<img src="'.(new QRCode)->render($data).'" alt="QR Code" />';

	echo "<br>";
	echo "<br>";
	
	//substring
	$match=("");
	preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $output, $match);
	echo "<br>";
	echo '<div>Substring:</div>';
	echo "<br>";
	echo '<div>'.$match[0][0].'</div>';
	

	//Escape data
	echo "<br>";
	echo '<div>Escaped:</div>';
	echo "<br>";
	echo "<br>";
	echo '<div>'.htmlspecialchars_decode($match[0][0]).'</div>';

}

function auth($username, $password, $domain = 'evncpc.vn', $endpoint = 'ldap://evncpc.vn:389', $dc = 'dc=evncpc,dc=vn') {
	$ldap = @ldap_connect($endpoint);
	if(!$ldap) return false;

	if(!strpos($username, '@')) {
		$userName = $username."@".$domain;
	}
	else $userName=$username;
	ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

	$bind = @ldap_bind($ldap, $userName, $password);
	if(!$bind) return false;

	$result = @ldap_search($ldap, $dc, "(sAMAccountName=$username)");
	if(!$result) return false; 

	@ldap_sort($ldap, $result, 'sn');
	$info = @ldap_get_entries($ldap, $result);
	if(!$info) return false;
	if(!isset($info['count']) || $info['count'] !== 1) return false;

	$data = [];

	foreach($info[0] as $key => $value) {
		if(is_numeric($key)) continue;
		if($key === 'count') continue;

		$data[$key] = (array)$value;
		unset($data[$key]['count']);
	}

	return [
		'mail' => $data['mail'][0],
		'displayname' => $data['displayname'][0]
	];
}
?><!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<title>QR Code</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
	<style>
		form {max-width: 300px;margin:auto}
		input {margin-bottom:10px}
	</style>
</head>
<body>
	<div class="container">
		<h2 class="text-center">ĐĂNG NHẬP ĐỂ TẠO QR CODE</h2>
		<?php if(empty($_POST['username']) || empty($_POST['password'])) { ?>

		<form method="POST">
		  <div class="mb-3">
		    <!-- <label for="inputAccount" class="form-label">Tài khoản</label> -->
		    <input type="text" name="username" class="form-control" id="inputAccount" aria-describedby="helpAccount" placeholder="tài khoản" required>
		    <div id="helpAccount" class="form-text">Đăng nhập sử dụng tài khoản eOffice, tài khoản máy tính</div>
		  </div>
		  <div class="mb-3">
		    <!-- <label for="inputPassword" class="form-label">Mật khẩu</label> -->
		    <input type="password" name="password" class="form-control" id="inputPassword" placeholder="mật khẩu" required>
		  </div>
		  <button type="submit" class="btn btn-primary">Đăng nhập</button>
		</form>
		<?php } else {
			$info = auth($_POST['username'], $_POST['password']);
			if(!$info) echo '<div class="alert alert-danger text-center">Vui lòng thử lại, tài khoản hoặc mật khẩu chưa đúng!</div>';
			else {
				echo '<div class="alert alert-success text-center">Login success</div>';
				echo '<br>';
				genqr($_POST['username'],$_POST['password']);
				// header("Location: http://10.72.107.98/webconsole.php", true, 301);
				// exit();
			}
		}
		?>
	</div>
</body>
</html>
