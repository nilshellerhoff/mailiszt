<?php

require_once('Base.php');

class Group extends Base {
    public $table = "_group";
    public $identifier = "i_group";

    public $exposedInfo = [
        "READER"    => ["i_group", "s_name", "d_inserted", "d_updated"],
        "ADMIN"     => ["i_group", "s_name", "d_inserted", "d_updated"],
    ];

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

    public function getMembers() {
        // get all the members of the group
        $db = new DB();
        return $db->queryAll("
            SELECT m.*
            FROM member m
            INNER JOIN member2group mg ON mg.i_member = m.i_member
            WHERE mg.i_group = ?
        ", [$this->properties["i_group"]]);
    }

    public function setMembers($member_ids) {
        // set the members of a group from array with member ids
        $db = new DB();

        // delete all existing members of the group
        $db->execute("DELETE FROM member2group WHERE i_group = ?", [$this->properties["i_group"]]);

        // add the members from the array
        foreach ($member_ids as $member_id) {
            $db->insert("member2group", [
                "i_group" => $this->properties["i_group"],
                "i_member" => $member_id
            ]);
        }
    }
}