<?php

function _hex2bin($data) {
  $len = strlen($data);
  return pack("H" . $len, $data);
}

function _makeDigest( $pwd, $nonce, $timestamp  ) {
  $txt = sha1($nonce.$timestamp.$pwd);
  $digest = base64_encode( _hex2bin($txt) );
  
  return $digest;
  
}

function _makeWsseHeader( $user, $pass) 
{
  $date = date('Y-m-d').'T'.date('H:i:s+01:00');
  $exp_min = date('i') + 5;
  if ($exp_min < 10)
    $exp_min = '0'.$exp_min;
  $expires = date('Y-m-d').'T'.date('H:').$exp_min.date(':s+01:00');
  $pass = md5($pass);
  $nonce = md5($user.$date);
  $digest = _makeDigest ($pass, $nonce, $date);
  return "X-WSSE: WSSE Username=\"$user\", PasswordDigest=\"$digest\", Nonce=\"$nonce\", Created=\"$date\", Expires=\"$expires\"\n";
}

function get ($server, $uri, $user, $pass)
{

  // construit la requête
  $request  = "GET $uri HTTP/1.0\n";
  $request .= _makeWsseHeader($user, $pass);
  $request .= "Accept: application/atom+xml\n\n";

  echo $request."<hr/>";
  // envoit la requête
  $fp = fsockopen($server, 80, &$errno, &$errstr, 30);
  fputs($fp,$request);

  // récupère la requête
  while ($line = fgets($fp, 4096)) {
    echo $line."<br/>\n";
  }
}

function post ($server, $uri, $user, $pass, $content)
{
  // construit la requête
  $request  = "POST $uri HTTP/1.0\n";
  $request .= "Content-Type: application/atom+xml\n";
  $request .= _makeWsseHeader($user, $pass);
  $request .= "Content-Length: ".strlen($content)."\n";
  $request .= "\n";
  $request .= $content."\n";
  $request .= "\n";  

  echo $request."<hr/>";
  // envoit la requête
  $fp = fsockopen($server, 80, &$errno, &$errstr, 30);
  fputs($fp,$request);

  // récupère la requête
  while ($line = fgets($fp, 4096)) {
    echo $line."<br/>\n";
  }
}

function put ($server, $uri, $user, $pass, $content)
{
  // construit la requête
  $request  = "PUT $uri HTTP/1.0\n";
  $request .= "Content-Type: application/atom+xml\n";
  $request .= _makeWsseHeader($user, $pass);
  $request .= "Content-Length: ".strlen($content)."\n";
  $request .= "\n";
  $request .= $content."\n";
  $request .= "\n";  

  echo $request."<hr/>";
  // envoit la requête
  $fp = fsockopen($server, 80, &$errno, &$errstr, 30);
  fputs($fp,$request);

  // récupère la requête
  while ($line = fgets($fp, 4096)) {
    echo $line."<br/>\n";
  }
}

function delete ($server, $uri, $user, $pass)
{

  // construit la requête
  $request  = "DELETE $uri HTTP/1.0\n";
  $request .= _makeWsseHeader($user, $pass);
  $request .= "Accept: application/atom+xml\n\n";

  echo $request."<hr/>";
  // envoit la requête
  $fp = fsockopen($server, 80, &$errno, &$errstr, 30);
  fputs($fp,$request);

  // récupère la requête
  while ($line = fgets($fp, 4096)) {
    echo $line."<br/>\n";
  }
}

?>