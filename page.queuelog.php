<?php

if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

$page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 'stats';
echo FreePBX::create()->Queuelog->showPage($page);