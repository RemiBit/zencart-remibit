<?php
chdir('../../');
require('includes/application_top.php');
$url = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', true, false);
$post_string = [];
foreach ($_POST as $key => $value) {
  $post_string[] = "<input type='hidden' name='$key' value='$value'/>";
}

$loading = ' <div style="width: 100%; height: 100%;top: 50%; padding-top: 10px;padding-left: 10px;  left: 50%; transform: translate(40%, 40%)"><div style="width: 150px;height: 150px;border-top: #CC0000 solid 5px; border-radius: 50%;animation: a1 2s linear infinite;position: absolute"></div> </div> <style>*{overflow: hidden;}@keyframes a1 {to{transform: rotate(360deg)}}</style>';

$html_form = '<form action="' . $url . '" method="post" id="authorize_payment_form">' . implode('', $post_string) . '<input type="submit" id="submit_authorize_payment_form" style="display: none"/>' . $loading . '</form><script>document.getElementById("submit_authorize_payment_form").click();</script>';

echo $html_form;
die();
?>