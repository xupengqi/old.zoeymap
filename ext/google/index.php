<?php
/* Copyright (c) 2009 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Author: Eric Bidelman <e.bidelman>
*/

session_start();

// OAuth/OpenID libraries and utility functions.
// OAuth libraries - http://oauth.googlecode.com/svn/code/php/
require_once('OAuth.php');


// Setup OAuth consumer with our "credentials"
//$CONSUMER_KEY = 'www.designshuffle.com';
//$CONSUMER_SECRET = 'YH8SoPYoQ/MXoeIWlpuz9HSr';
$CONSUMER_KEY = 'www.zoeymap.com';
$CONSUMER_SECRET = 'vgc0B+KaduiogulIMjggFMaw';
$consumer = new OAuthConsumer($CONSUMER_KEY, $CONSUMER_SECRET);


$openid_params = array(
  'openid.ns'                => 'http://specs.openid.net/auth/2.0',
  'openid.claimed_id'        => 'http://specs.openid.net/auth/2.0/identifier_select',
  'openid.identity'          => 'http://specs.openid.net/auth/2.0/identifier_select',
  'openid.return_to'         => "http://{$CONSUMER_KEY}{$_SERVER['PHP_SELF']}",
  'openid.realm'             => "http://{$CONSUMER_KEY}",
  'openid.mode'              => @$_REQUEST['openid_mode'],
  'openid.ns.ui'             => 'http://specs.openid.net/extensions/ui/1.0',
  'openid.ns.ext1'           => 'http://openid.net/srv/ax/1.0',
  'openid.ext1.mode'         => 'fetch_request',
  'openid.ext1.type.email'   => 'http://axschema.org/contact/email',
  'openid.ext1.type.first'   => 'http://axschema.org/namePerson/first',
  'openid.ext1.type.last'    => 'http://axschema.org/namePerson/last',
  'openid.ext1.type.country' => 'http://axschema.org/contact/country/home',
  'openid.ext1.type.lang'    => 'http://axschema.org/pref/language',
  'openid.ext1.required'     => 'email,first,last,country,lang',
  'openid.ns.oauth'          => 'http://specs.openid.net/extensions/oauth/1.0',
  'openid.oauth.consumer'    => $CONSUMER_KEY,
  'openid.oauth.scope'       => ''
);

$openid_ext = array(
  'openid.ns.ext1'           => 'http://openid.net/srv/ax/1.0',
  'openid.ext1.mode'         => 'fetch_request',
  'openid.ext1.type.email'   => 'http://axschema.org/contact/email',
  'openid.ext1.type.first'   => 'http://axschema.org/namePerson/first',
  'openid.ext1.type.last'    => 'http://axschema.org/namePerson/last',
  'openid.ext1.type.country' => 'http://axschema.org/contact/country/home',
  'openid.ext1.type.lang'    => 'http://axschema.org/pref/language',
  'openid.ext1.required'     => 'email,first,last,country,lang',
  'openid.ns.oauth'          => 'http://specs.openid.net/extensions/oauth/1.0',
  'openid.oauth.consumer'    => $CONSUMER_KEY,
  'openid.oauth.scope'       => '',
  'openid.ui.icon'           => 'true'
);


if (isset($_REQUEST['popup']) && !isset($_SESSION['redirect_to'])) {
  $query_params = '';
  if($_POST) {
    $kv = array();
    foreach ($_POST as $key => $value) {
      $kv[] = "$key=$value";
    }
    $query_params = join('&', $kv);
  } else {
    $query_params = substr($_SERVER['QUERY_STRING'], strlen('popup=true') + 1);
  }

  $_SESSION['redirect_to'] = "http://{$CONSUMER_KEY}{$_SERVER['PHP_SELF']}?{$query_params}";
  echo '<script type = "text/javascript">window.close();</script>';
  exit;
} else if (isset($_SESSION['redirect_to'])) {
  $redirect = $_SESSION['redirect_to'];
  unset($_SESSION['redirect_to']);
  header('Location: ' .$redirect);
}

switch(@$_REQUEST['openid_mode']) {
  case 'cancel':
    debug('Sign-in was cancelled.');
    break;
}



function debug($message) {
  echo "<div class=\"errors\">$message</div>";
}
?>

<html>
<head>
<title>Google Hybrid Protocol Demo (OpenID + OAuth)</title>
<script src="http://code.jquery.com/jquery-latest.min.js"></script>
<script type="text/javascript" src="popuplib.js"></script>
<script type="text/javascript">
  var upgradeToken = function() {
    window.location = '<?php if(isset($_SESSION['redirect_to'])) echo $_SESSION['redirect_to']; ?>';
  };
  var extensions = <?php echo json_encode($openid_ext); ?>;
  var googleOpener = popupManager.createPopupOpener({
    'realm' : '<?php echo $openid_params['openid.realm'] ?>',
    'opEndpoint' : 'https://www.google.com/accounts/o8/ud',
    'returnToUrl' : '<?php echo $openid_params['openid.return_to'] . '?popup=true' ?>',
    'onCloseHandler' : upgradeToken,
    'shouldEncodeUrls' : true,
    'extensions' : extensions
  });
  $(document).ready(function () {
    jQuery('#LoginWithGoogleLink').click(function() {
      googleOpener.popup(450, 500);
      return false;
    });
  });
</script>
</head>
<body>
<div>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<fieldset>
  Sign in with a
  <a href="<?php echo $_SERVER['PHP_SELF'] . '?openid_mode=checkid_setup&openid_identifier=google.com/accounts/o8/id' ?>" id="LoginWithGoogleLink"><span class="google"><span>G</span><span>o</span><span>o</span><span>g</span><span>l</span><span>e</span> Account</span></a> (popup)
</fieldset>
</form>
</div>

<?php if(@$_REQUEST['openid_mode'] === 'id_res'): ?>
  <p>
  Welcome: <?php echo "{$_REQUEST['openid_ext1_value_first']} {$_REQUEST['openid_ext1_value_last']} - {$_REQUEST['openid_ext1_value_email']}" ?><br>
  country: <?php echo $_REQUEST['openid_ext1_value_country'] ?><br>
  language: <?php echo $_REQUEST['openid_ext1_value_lang'] ?><br>
  </p>
<?php endif; ?>

</body>
</html>

