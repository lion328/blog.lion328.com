<?php
defined('MYBLOG') || die();

class User {
	
	private $username;
	public $canPublishContent;
	private $passwordHash;
	
	public function __construct($username) {
		$this->username = $username;
	}
	
	private function getPasswordHash($password) {
		return hash("sha256", MYBLOG_PASSWORD_SALT . $password);
	}
	
	public function setPassword($password) {
		$this->passwordHash = $this->getPasswordHash($password);
	}
	
	public function load() {
		$file = @fopen(MYBLOG_BASEPATH . "/data/auth-{$this->username}", 'r');
		if($file === false) return;
		$this->canPublishContent = fgets($file) == 1;
		$this->passwordHash = fgets($file);
		@fclose($file);
	}
	
	public function save() {
		$file = @fopen(MYBLOG_BASEPATH . "/data/auth-{$this->username}",'w');
		@fwrite($file,($this->canPublishContent ? 1 : 0)."\n");
		@fwrite($this->passwordHash);
		@fclose($file);
	}
	
    public function getUsername() {
        return $this->username;
    }
    
    public static function logout() {
        if(self::getCurrentUser() !== false) unset($_SESSION['myblog_username']);
    }
    
	public function authentication($password) {
		if($this->getPasswordHash($password) == $this->passwordHash) {
			$_SESSION['myblog_username'] = $this->username;
			return true;
		}
		return false;
	}
    
    public static function authentication_static($user, $password) {
        $c = new User($user);
        if($c->load() === false) return false;
        return $c->authentication($password);
    }
	
	public static function getCurrentUser() {
        if(isset($_SESSION['myblog_username'])) {
            $user = new User($_SESSION['myblog_username']);
            if($user->load() === false) return false;
            return $user;
        }
		return false;
	}
    
    public static function getCurrentUsername() {
        $current_user = self::getCurrentUser();
        return $current_user === false ? false : $current_user->getUsername();
    }
}
?>