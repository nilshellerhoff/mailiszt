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
        return $db->queryColumn("
            SELECT g.s_name
            FROM _group g
            INNER JOIN member2group mg ON mg.i_group = g.i_group
            WHERE mg.i_member = ?
        ", [$this->properties["i_member"]]);
    }

    public function setGroups($groups) {
        // set the groups of user from array with group names
        $db = new DB();

        // delete all existing groups
        $db->execute("DELETE FROM member2group WHERE i_member = ?", [$this->properties["i_member"]]);

        // add groups from array
        foreach ($groups as $group) {
            $db->execute("
                INSERT INTO member2group (i_member, i_group, d_inserted)
                VALUES (?, (SELECT i_group FROM _group WHERE s_name = ?), DATETIME('now'))
            ", [$this->properties["i_member"], $group]);
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
}