<?php
define('MYBLOG', true);
define('MYBLOG_ALLOW_COMMENT', false);
define('MYBLOG_ALLOW_REGISTER', false);
define('MYBLOG_VERSION', '1.4.5');
define('MYBLOG_BASEPATH', dirname(__FILE__));
define('MYBLOG_PASSWORD_SALT', 'salt');
define('MYBLOG_DATABASE_VERSION', '1.2');
define('MYBLOG_ENTRIES_PER_PAGE', 10);

date_default_timezone_set('Asia/Bangkok');

require_once('libs/MarkdownExtra.inc.php');
require_once('libs/Parsedown.php');
require_once('markdown.php');

$MYBLOG_MARKDOWN_PARSER = new ParsedownMarkdownParser();

require_once('content.php');
require_once('user.php');

session_start();
?>
