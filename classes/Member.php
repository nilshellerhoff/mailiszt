<?php

require_once('Base.php');

class Member extends Base {
    public static $table = "member";
    public static $identifier = "i_member";

    public $exposedInfo = [
        "READER"    => ["i_member", "s_name1", "s_name2", "s_email", "s_phone", "d_birthdate", "b_active", "d_inserted", "d_updated"],
        "ADMIN"     => ["i_member", "s_name1", "s_name2", "s_email", "s_phone", "d_birthdate", "b_active", "d_inserted", "d_updated"],
    ];


    public function getGroups() {
        // get groups of member
        $db = new DB();
        return $db->queryAll("
            SELECT g.*
            FROM _group g
            INNER JOIN member2group mg ON mg.i_group = g.i_group
            WHERE mg.i_member = ?
        ", [$this->properties["i_member"]]);
    }

    public function setGroups($group_ids) {
        // set the groups of user from array with group ids
        $db = new DB();

        $lists = 

        // delete all existing groups
        $db->execute("DELETE FROM member2group WHERE i_member = ?", [$this->properties["i_member"]]);

        // add groups from array
        foreach ($group_ids as $group_id) {
            $db->execute("
                INSERT INTO member2group (i_member, i_group, d_inserted)
                VALUES (?, ?, DATETIME('now'))
            ", [$this->properties["i_member"], $group_id]);
        }
    }

    public function afterDelete() {
        // delete all entries from member2group which point to the member
        $db = new DB();
        $db->delete(
            "member2group",
            ["i_member" => $this->properties["i_member"]]
        );
    }

    public function apiGetAddInfo($member) {
        $member["groups"] = $this->getGroups();
        return $member;
    }
}