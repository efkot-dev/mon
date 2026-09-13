<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

$auth->error();

$cms_headers = [
"X-Powered-CMS: Joomla 3.9",
"X-Engine: DataLife Engine 14.0",
"X-Generator: WordPress 6.0",
"X-Drupal-Cache: HIT",
"X-Powered-By: OpenCart 3.0",
"X-Powered-By: MODX Revolution",
"X-Powered-By: Bitrix Framework",
"X-Powered-By: Laravel",
"X-Powered-By: Symfony",
"X-Powered-By: Yii2",
"X-CMS: Prestashop Engine",
"X-Content-Management: Magento",
"X-Framework: CodeIgniter",
"X-Generator: TYPO3 CMS",
"X-Application: phpBB",
"X-Engine: vBulletin",
"X-Engine: XenForo",
];

header($cms_headers[array_rand($cms_headers)]);
?>

<!DOCTYPE html>
<html lang="uk">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>ISP System Monitoring</title>

<link rel="icon" type="image/png" sizes="16x16" href="../style/img/favicon-16x16.png">

<style>
.errorlogin{
position: absolute;
    background: #ffff;
    padding: 10px 20px;
    top: 10%;
    border: 3px solid red;
    border-radius: 10px;
    color: red;
	}
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family: system-ui, -apple-system, sans-serif;
}

body{
background: linear-gradient(135deg,#0f2027,#203a43,#2c5364);
height:100vh;
display:flex;
align-items:center;
justify-content:center;
}

.login-container{
width:100%;
max-width:420px;
padding:20px;
}

.login-box{

background:white;
padding:35px;
border-radius:14px;
box-shadow:0 15px 40px rgba(0,0,0,0.3);

}

.login-title{
text-align:center;
font-size:24px;
font-weight:600;
margin-bottom:25px;
color:#333;
}

.input-group{
margin-bottom:18px;
}

.input-group label{
display:block;
font-size:14px;
margin-bottom:6px;
color:#666;
}

.input-group input{

width:100%;
padding:12px 14px;

border-radius:8px;
border:1px solid #ddd;

font-size:15px;
transition:0.2s;

}

.input-group input:focus{

border-color:#4a90e2;
outline:none;
box-shadow:0 0 0 2px rgba(74,144,226,0.15);

}

.btn-login{

width:100%;
padding:13px;

border:none;
border-radius:8px;

background:#4a90e2;
color:white;

font-size:16px;
font-weight:600;

cursor:pointer;

transition:0.2s;

}

.btn-login:hover{
background:#3d7dc4;
}

.footer{

margin-top:18px;
text-align:center;
font-size:12px;
color:#888;

}

@media (max-width:480px){

.login-box{
padding:25px;
}

.login-title{
font-size:20px;
}

}

</style>

</head>

<body>

<div class="login-container">

<div class="login-box">

<div class="login-title">
ISP System Monitoring
</div>

<form method="post" action="/?do=login">

<div class="input-group">
<label>Login</label>
<input type="text" name="username" required>
</div>

<div class="input-group">
<label>Password</label>
<input type="password" name="password" required>
</div>

<input type="hidden" name="login" value="login">

<button class="btn-login" type="submit">
Login
</button>

</form>

<div class="footer">
Monitor all your devices from one place
</div>

</div>

</div>

</body>
</html>

<?php
die;
?>