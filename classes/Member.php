<?php

require_once('Base.php');

class Member extends Base {
    public $table = "member";
    public $identifier = "i_member";

    public $exposedInfo = [
        "READER"    => ["i_member", "s_name1", "s_name2", "s_email", "s_phone", "d_birthdate", "b_active", "d_inserted", "d_updated"],
        "ADMIN"     => ["i_member", "s_name1", "s_name2", "s_email", "s_phone", "d_birthdate", "b_active", "d_inserted", "d_updated"],
    ];


    public function getGroups() {
        // get groups of member
        $db = new DB();
        return $db->queryAll("
            SELECT *
            FROM _group g
            INNER JOIN member2group mg ON mg.i_group = g.i_group
            WHERE mg.i_member = ?
        ", [$this->properties["i_member"]]);
    }
}