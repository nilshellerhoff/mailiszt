<template>
  <MailboxPopup
    @save="saveMailbox()"
    :popupTitle="`Add mailbox`"
    :btnState="btnState"
    :mailbox="mailbox"
    :groupsAvail="groupsAvail"
    :groups="groups"
    :groupsLogic="groupsLogic"
  >
  </MailboxPopup>
</template>

<script>
import MailboxPopup from "@/components/MailboxPopup.vue";

export default {
  name: "AddMailbox",
  data: function () {
    return {
      mailbox: {},
      btnState: "ready",
      groupsAvail: [
        {
          text: "Group",
          value: "i_group",
          type: "item",
          items: [{ text: "", value: "" }],
        },
      ],
      groups: { groups : [] },
      groupsLogic: {
        id: 1631607982497,
        comparisonOperator: "=",
        value: 1,
        entity: "i_group",
        logicOperator: "none",
        arguments: [],
      },
    };
  },
  components: {
    MailboxPopup,
  },
  methods: {
    getGroupsAvail() {
      this.$api.get(`group`).then((response) => {
        this.groupsAvail[0].items = response.data.map((group) => ({
          value: group.i_group,
          text: group.s_name,
        }));
      });
    },
    async saveMailbox() {
      this.btnState = "loading";
      this.mailbox.j_groups = JSON.stringify(this.groups.groups)
      this.mailbox.j_groupslogic = JSON.stringify(this.groupsLogic)

      this.$api.put(`/mailbox/add`, this.mailbox).then((response) => {
        // populate the element with the new data
        this.mailbox = response.data
          this.btnState = "done";
          setTimeout(() => {
            this.$root.$emit("reloadData");
            this.$router.back();
          }, 500);
        })
    },
  },
  mounted() {
    this.getGroupsAvail();
  },
};
</script>