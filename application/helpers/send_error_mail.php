<?php

function send_error_mail($method, $code, $type, $msg)
{
	mail('rave@wkndhllr.com', 'Error in ' . $method, 'Code: ' . $code . '; Type: ' . $type . '; Message: ' . $msg);
}