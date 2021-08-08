<?php

require_once('Base.php');

class Group extends Base {
    public $table = "_group";
    public $identifier = "i_group";

    public $exposedInfo = [
        "READER"    => ["i_group", "s_name", "d_inserted", "d_updated"],
        "ADMIN"     => ["i_group", "s_name", "d_inserted", "d_updated"],
    ];

    public function getMembers() {
        // get members of group
        $db = new DB();
        return $db->queryAll("
            SELECT *
            FROM member m
            INNER JOIN member2group mg ON mg.i_member = m.i_member
            WHERE mg.i_group = ?
        ", [$this->properties["i_group"]]);
    }

    public function insertMember($i_member) {
        $db = new DB();

        // see if member-group connection exists, if yes abort
        if ($db->queryScalar(
            "SELECT 1 FROM member2group WHERE i_group = ? AND i_member = ?",
            [$this->properties["i_group"], $i_member]
        )) {
            return;
        }

        // else add a member to the group
        $db->insert(
            "member2group",
            [
                "i_member"  => $i_member,
                "i_group"   => $this->properties["i_group"]
            ]
        );
    }
}