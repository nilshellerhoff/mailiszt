<?php

require_once('Base.php');

class Member extends Base {
    public $table = "member";
    public $identifier = "i_member";

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