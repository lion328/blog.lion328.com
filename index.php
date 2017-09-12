<?php
require_once('config.php');

$allow_pages = array('post', 'login', 'logout', 'entry');
$current_page = 'index';

foreach($_GET as $k => $v) {
    if(in_array($k, $allow_pages)) {
        $current_page = $k;
        break;
    }
}

function print_entry($entry) {
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <a class="blog_entry_title" href="<?php echo $_SERVER['PHP_SELF']; ?>?entry=<?php echo $entry->getID(); ?>"><h3 class="panel-title"><?php echo $entry->title; ?></h3></a>
    </div>
    <div class="panel-body markdown-body">
        <?php echo $entry->content; ?>
    </div>
    <div class="panel-footer">เขียนโดย <?php echo $entry->writer; if($entry->getTimestamp() !== false) echo " เมื่อวันที่ " . date("d-m-Y เวลา H:i:s", $entry->getTimestamp()); ?></div>
</div>
<?php
}
ob_start();
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="css/default.css">
		<link rel="stylesheet" type="text/css" href="css/github-markdown.css">
        <script src="js/legacy_hash_link.js"></script>
        <title>lion328's Blog</title>
    </head>
    <body>
        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?php echo $_SERVER['PHP_SELF']; ?>">lion328's Blog</a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li<?php if($current_page == 'index') echo ' class="active"'; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>"><span class="glyphicon glyphicon-home"></span>&nbsp;&nbsp;หน้าหลัก</a></li>
                        <?php if(User::getCurrentUsername() === false) { ?>
                        <li<?php if($current_page == 'login') echo ' class="active"'; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?login"><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;เข้าสู่ระบบ</a></li>
                        <?php } else { ?>
                        <li<?php if($current_page == 'post') echo ' class="active"'; ?>><a href="?post"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;เขียน</a></li>
                        <li<?php if($current_page == 'logout') echo ' class="active"'; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?logout"><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;ออกจากระบบ</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="container">
            <header>
                <div class="blog_head">
                    <div class="blog_title">lion328's Blog</div>
                    <div class="blog_descrption">Blog กากๆ ของคนกากๆ</div>
                </div>
            </header>
            
            <div class="row">
                <div class="col-md-9">
                    <div class="blog_content">
                        <?php
                            switch($current_page) {
                                default:
                                case 'index':
                                $page = 1;
                                if(isset($_GET['page'])) {
                                    if(is_numeric($_GET['page'])) $page = intval($_GET['page']);
                                    else {
                                        header("Location: ?page=1");
                                        die();
                                    }
                                }
                                $max_page = ceil(Content::getLatestID() / MYBLOG_ENTRIES_PER_PAGE);
                                if($page > $max_page) {
                                    header("Location: {$_SERVER['PHP_SELF']}?page={$max_page}");
                                    die();
                                } elseif($page < 1) {
                                    header("Location: ?page=1");
                                    die();
                                }
                                if(Content::getLatestID() !== 0) {
                                    $end = Content::getLatestID() - (MYBLOG_ENTRIES_PER_PAGE * ($page - 1)) + 1;
                                    $start = $end - MYBLOG_ENTRIES_PER_PAGE;
                                    if($start < 0) $start = 0;
                                    $entries = array();
                                    for($i = $start; $i < $end; $i++) {
                                        $entry = new Content($i);
                                        if($entry->load() === false) continue;
                                        $entries[] = $entry;
                                    }
                                } else {
                                    echo '<div class="alert alert-warning text-center" role="alert">ไม่มีโพสที่จะแสดง</div>';
                                    break;
                                }
                                foreach(array_reverse($entries) as $entry) print_entry($entry);
                        ?>
                        <ul class="pager text-center">
                            <?php if($page !== 1) { ?><li class="previous"><a href="<?php echo "{$_SERVER['PHP_SELF']}?page=" . ($page - 1); ?>">&larr; ก่อนหน้า</a></li><?php } ?>
                            <?php if($page != $max_page) { ?><li class="next"><a href="<?php echo "{$_SERVER['PHP_SELF']}?page=" . ($page + 1); ?>">ถัดไป &rarr;</a></li><?php } ?>
                        </ul>
                        <?php
                                break;
                                case 'post':
                                $current_user = User::getCurrentUser();
                                if(($current_user !== false) && $current_user->canPublishContent) {
                                    if(isset($_POST['title'], $_POST['contents'])) {
                                        $id = Content::getLatestID() + 1;
                                        $content = new Content($id);
                                        $content->content = $_POST['contents'];
                                        $content->writer = User::getCurrentUsername();
                                        $content->title = $_POST['title'];
                                        $s = $content->save();
                                        if($s === false) {
                                            echo '<div class="alert alert-danger text-center" role="alert">ไม่สามารถบันทึกได้ กรุณาลองใหม่</div>';
                                            break;
                                        }
                                        Content::setLatestID($id);
                                        echo '<div class="alert alert-success text-center" role="alert">ทำการโพสเรียบร้อยแล้ว <a href="' . $_SERVER['PHP_SELF'] . '?entry=' . $id . '">สามารถดูได้ที่นี่</a></div>';
                                    } else {
                                ?>
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title">เขียน</h3></a>
                            </div>
                            <div class="panel-body">
                                <form class="form-horizontal" role="form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?post">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <label for="title" class="col-sm-2 control-label">ชื่อเรื่อง</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="title" name="title" placeholder="ชื่อเรื่อง" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-10 col-sm-offset-1">
                                                <textarea type="text" class="form-control blog_textarea" id="contents" name="contents" placeholder="เนื้อหา" required></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2 col-sm-offset-9">
                                                <input type="submit" class="form-control btn btn-primary" value="โพส">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                                <?php
                                    }
                                } else {
                                    echo '<div class="alert alert-danger text-center" role="alert">คุณไม่มีสิทธิ์ที่จะเขียน!</div>';
                                    break;
                                }
                                break;
                                case 'login':
                                if(isset($_POST['user'], $_POST['password']) && User::getCurrentUser() === false) {
                                    $vaild = User::authentication_static($_POST['user'], $_POST['password']);
                                    if($vaild) {
                                        echo '<div class="alert alert-success text-center" role="alert">คุณได้เข้าสู่ระบบเรียบร้อยแล้ว ระบบจะพาคุณไปสู่หน้าหลักอัตโนมัติภายใน 2 วินาที หากยังอยู่หน้าเดิมกรุณา<a href="' . $_SERVER['PHP_SELF'] . '">กดทีนี่</a></div>';
                                        echo "<meta http-equiv=\"refresh\" content=\"2; url={$_SERVER['PHP_SELF']}\">";
                                        break;
                                    } else echo '<div class="alert alert-danger text-center" role="alert">ชื่อหรือรหัสผ่านผิด กรุณาลองใหม่</div>';
                                } elseif(User::getCurrentUser() !== false) {
                                    echo '<div class="alert alert-danger text-center" role="alert">คุณได้เข้าสู่ระบบอยู่แล้ว!</div>';
                                    break;
                                }
                        ?>
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title">เข้าสู่ระบบ</h3></a>
                            </div>
                            <div class="panel-body">
                            <form class="form-horizontal" role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>?login" method="post">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <label for="user" class="col-sm-2 control-label">ชื่อผู้ใช้</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="user" name="user" placeholder="ชื่อผู้ใช้" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="password" class="col-sm-2 control-label">รหัสผ่าน</label>
                                            <div class="col-sm-9">
                                                <input type="password" class="form-control" id="password" name="password" placeholder="รหัสผ่าน" required>                                   </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-offset-2 col-sm-9">
                                                <input type="submit" class="btn btn-primary pull-right" value="เข้าสู่ระบบ">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <?php
                                break;
                                case 'logout':
                                if(User::getCurrentUsername() === false) {
                                    echo '<div class="alert alert-danger text-center" role="alert">คุณยังไม่ได้เข้าสู่ระบบ!</div>';
                                    break;
                                }
                                User::logout();
                        ?>
                        <div class="alert alert-success text-center" role="alert">คุณออกจากระบบเรียบร้อยแล้ว</div>
                        <?php
                                break;
                                case 'entry':
                                $id = null;
                                if(is_numeric($_GET['entry'])) $id = intval($_GET['entry']);
                                else goto error;
                                $entry = new Content($id);
				$flag = true;
				heyy:
                                if($entry->load() === false) {
				    if($flag) {
					$entry = new Content("{$id}x");
				    	$flag = false;
					goto heyy;
				    }
                                    error:
                                    echo '<div class="alert alert-danger text-center" role="alert">ไม่สามารถแสดงโพสนี้ได้</div>';
                                    break;
                                }
                                print_entry($entry);
                                break;
                            }
                        ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="blog_sidebar">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title">เกี่ยวกับเรา</h3></a>
                            </div>
                            <div class="panel-body">
                                <img class="center-block blog_about_avatar" src="img/avatar.jpg">
                                <h4>lion328</h4>
                                <p>[REDACTED]</p>
                                <p>
                                    <a href="https://www.facebook.com/profile.php?id=100002406944802">Facebook</a><br>
									<a href="https://instagram.com/dotlegs">Instagram</a><br>
                                    <a href="https://twitter.com/dotlegs">Twitter</a><br>
                                </p>
                            </div>
                        </div>
                        <?php if(User::getCurrentUsername() === false && $current_page != 'login') { ?>
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title">เข้าสู่ระบบ</h3></a>
                            </div>
                            <div class="panel-body">
                                <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>?login" method="post">
                                    <div class="form-group">
                                        <div class="form-group"><input type="text" class="form-control" id="user" name="user" placeholder="ชื่อผู้ใช้" required></div>
                                        <div class="form-group"><input type="password" class="form-control" id="password" name="password" placeholder="รหัสผ่าน" required></div>
                                        <div class="form-group"><input type="submit" class="btn btn-primary pull-right" value="เข้าสู่ระบบ"></div>
                                    </div>
                                </form>
                            </div>
                        </div><?php } elseif(User::getCurrentUsername() !== false) { ?>
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title">ผู้ใช้งาน</h3></a>
                            </div>
                            <div class="panel-body">
                                <p><strong>ผู้ใช้งาน: </strong><?php echo User::getCurrentUsername(); ?></p>
                                <a class="btn btn-primary" href="<?php echo $_SERVER['PHP_SELF']; ?>?logout">ออกจากระบบ</a>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            
            <footer>
                <div class="blog_footer">
                    <p class="text-center text-muted">lion328's Blog เวอร์ชัน <?php echo MYBLOG_VERSION; ?> พัฒนาโดย lion328</p>
                </div>
            </footer>
        </div>
        
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
    </body>
</html>
<?php echo ob_get_clean(); ?>
