<?php
declare(strict_types=1);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function respond(int $code, string $message): never { http_response_code($code); echo json_encode(['ok'=>$code<400,'message'=>$message]); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405, 'Method not allowed.');
if (strlen(file_get_contents('php://input') ?: '') > 40000) respond(413, 'Form submission too large.');
ini_set('session.use_strict_mode','1');
ini_set('session.cookie_httponly','1');
ini_set('session.cookie_samesite','Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ini_set('session.cookie_secure','1');
session_start();
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin && !in_array($origin, ['https://driver4u.co.za','https://www.driver4u.co.za'], true)) respond(403,'Invalid request origin.');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) respond(403,'Security session expired. Refresh and try again.');
if (!empty($_POST['website'])) respond(200,'Thank you for your message.');
$now=time();
if (isset($_SESSION['last_submit']) && $now - (int)$_SESSION['last_submit'] < 45) respond(429,'Please wait before submitting another message.');
$cfgPath=dirname(__DIR__).'/config/mail.php';
if (!is_file($cfgPath)) respond(503,'Contact form is not configured yet. Please contact us by email.');
$cfg=require $cfgPath;
foreach (['smtp_host','smtp_username','smtp_password','recaptcha_secret','to_email'] as $key) {
    if (empty($cfg[$key]) || str_contains((string)$cfg[$key],'REPLACE_') || str_contains((string)$cfg[$key],'ADD_YOUR')) respond(503,'Contact form is not configured yet. Please contact us by email.');
}
function field(string $key,int $max): string {
 $v=$_POST[$key] ?? ''; if (!is_string($v)) respond(422,'Invalid form data.');
 $v=trim($v); if (mb_strlen($v)>$max) respond(422,'One or more fields are too long.');
 return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u','',$v) ?? '';
}
$type=field('form_type',15);
if (!in_array($type,['contact','driver'],true)) respond(422,'Unknown form type.');
$name=field('name',100);$email=field('email',190);$phone=field('phone',30);$message=field('message',5000);
if (mb_strlen($name)<2 || !filter_var($email,FILTER_VALIDATE_EMAIL) || !preg_match('/^[+0-9() .-]{7,30}$/',$phone) || mb_strlen($message)<10) respond(422,'Please check the required fields.');
if (field('consent',5)!=='yes') respond(422,'Please accept the privacy notice.');
$extras=[];
if ($type==='contact') {
 $subject=field('subject',50);
 if (!in_array($subject,['Vehicle owner partnership','Fleet management','General enquiry','Other'],true)) respond(422,'Choose an enquiry type.');
 $extras['Enquiry type']=$subject;
} else {
 $area=field('area',120);$licence=field('licence',5);$experience=field('experience',30);
 if (!$area || !in_array($licence,['Yes','No'],true) || !in_array($experience,['None yet','Less than 1 year','1–3 years','More than 3 years'],true)) respond(422,'Please complete the driver fields.');
 $extras=['Area'=>$area,'Valid driving licence'=>$licence,'E-hailing experience'=>$experience];
}
$token=field('recaptcha_token',4096);
if (!$token) respond(422,'Spam verification is missing. Please try again.');
if (!function_exists('curl_init')) respond(503,'Server configuration needs cURL support.');
$ch=curl_init('https://www.google.com/recaptcha/api/siteverify');
curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(['secret'=>$cfg['recaptcha_secret'],'response'=>$token]),CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,CURLOPT_CONNECTTIMEOUT=>5]);
$raw=curl_exec($ch);$http=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
if ($raw===false || $http!==200) respond(503,'Spam verification is temporarily unavailable. Please try again.');
$check=json_decode($raw,true);
$host=(string)($check['hostname'] ?? '');
$allowedHosts=[(string)$cfg['recaptcha_hostname'],'www.'.(string)$cfg['recaptcha_hostname']];
if (empty($check['success']) || ($check['action'] ?? '')!==$type || !in_array($host,$allowedHosts,true) || (float)($check['score'] ?? 0)<(float)($cfg['recaptcha_threshold'] ?? 0.5)) respond(403,'Spam verification failed. Please try again or email us directly.');
$autoload=dirname(__DIR__).'/vendor/autoload.php';
if (!is_file($autoload)) respond(503,'Mail service is not installed. Please contact us by email.');
require $autoload;
$lines=['Form: '.($type==='driver'?'Driver application':'Contact enquiry'),'Name: '.$name,'Email: '.$email,'Phone: '.$phone];
foreach($extras as $key=>$value)$lines[]=$key.': '.$value;
$lines[]='Message:';$lines[]=$message;
try {
 $mail=new PHPMailer(true);
 $mail->isSMTP();$mail->Host=$cfg['smtp_host'];$mail->SMTPAuth=true;
 $mail->Username=$cfg['smtp_username'];$mail->Password=$cfg['smtp_password'];$mail->Port=(int)$cfg['smtp_port'];
 $mail->SMTPSecure=($cfg['smtp_encryption']==='ssl')?PHPMailer::ENCRYPTION_SMTPS:PHPMailer::ENCRYPTION_STARTTLS;
 $mail->CharSet='UTF-8';$mail->setFrom($cfg['from_email'],$cfg['from_name']);
 $mail->addAddress($cfg['to_email'],$cfg['to_name']);$mail->addReplyTo($email,$name);
 $mail->Subject=($type==='driver'?'Drive4U Driver Interest':'Drive4U Website Enquiry').' — '.$name;
 $mail->isHTML(false);$mail->Body=implode("\n",$lines);$mail->send();
 $_SESSION['last_submit']=$now;$_SESSION['csrf_token']=bin2hex(random_bytes(32));
 respond(200,'Thank you! Your message has been sent. Our team will be in touch.');
} catch (Exception $e) {
 error_log('Drive4U mail send failure: '.$e->getMessage());
 respond(503,'We could not send your message right now. Please email info@driver4u.co.za.');
}
