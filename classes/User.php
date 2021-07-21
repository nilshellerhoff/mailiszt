<?php

require_once('Base.php');

class User extends Base {
    public $table = "user";
    public $identifier = "i_user";

    public $exposedInfo = [
        "READER"    => [],
        "ADMIN"     => ["i_user", "s_username", "d_inserted", "d_updated"]
    ];

    public function updatePassword($password) {
        $this->properties["s_password"] = $this->generateHash($password);
    }

    public static function idFromName($s_username) {
        $db = new DB();
        return $db->queryScalar("SELECT i_user FROM user WHERE s_username = ?", [$s_username]);
    }

    public static function generateHash($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function checkPassword($i_user, $password) {
        $db = new DB();
        $hash = $db->queryScalar("SELECT s_password FROM user WHERE i_user = ?", [$i_user]);
        return password_verify($password, $hash);
    }
    
    public static function authenticate($username, $password) {
        // authenticate user and create login token   
        $i_user = User::idFromName($username);
        if (self::checkPassword($i_user, $password)) {
            $token = bin2hex(random_bytes(32));
            $db = new DB();
            $db->insert("authtoken", [
                    "i_user"        => $i_user,
                    "s_username"    => $username,
                    "s_token"       => $token
            ]);
            return $token;
        }
        return false;
    }

    public static function checkAuthentication($username, $token) {
        // check if authToken is valid
        $db = new DB();
        $tokenCreated = $db->queryScalar("SELECT d_inserted FROM authtoken WHERE s_token = ?", [$token]);
        if (isset($tokenCreated)) {
            return true;
        }
        return false;
    }
}
