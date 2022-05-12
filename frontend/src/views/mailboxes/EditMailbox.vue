<template>
  <MailboxPopup
    @save="saveMailbox()"
    :popupTitle="`Edit mailbox '${this.mailbox.s_name}'`"
    :btnState="btnState"
    :mailbox="mailbox"
    :groupsAvail="groupsAvail"
    :groups="groups"
    :groupsLogic="groupsLogic"
    :allowedSendersPeople="allowedSendersPeople"
  >
  </MailboxPopup>
</template>

<script>
import MailboxPopup from "@/components/MailboxPopup.vue";

export default {
  name: "EditMailbox",
  data: function () {
    return {
      mailboxId: this.$route.params.id,
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
        "id": 1635960403820,
        "logicOperator": "",
        "comparisonOperator": "=",
        "entity": "i_group",
        "value": 1,
        "arguments": []
      },
      allowedSendersPeople: { people : [] },
    };
  },
  components: {
    MailboxPopup,
  },
  methods: {
    getMailbox() {
      this.$api.get(`/mailbox/${this.mailboxId}`).then((response) => {
        this.mailbox = response.data;

        // convert json types into objects
        this.groups = { groups : JSON.parse(this.mailbox.j_groups) }
        this.groupsLogic = JSON.parse(this.mailbox.j_groupslogic)
        this.allowedSendersPeople.people = JSON.parse(this.mailbox.j_allowedsenderspeople)
      });
    },
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

      // convert json types into string so they go into DB
      this.mailbox.j_groups = JSON.stringify(this.groups.groups)
      this.mailbox.j_groupslogic = JSON.stringify(this.groupsLogic)
      this.mailbox.j_allowedsenderspeople = JSON.stringify(this.allowedSendersPeople.people)

      this.$api.put(`/mailbox/${this.mailboxId}`, this.mailbox).then(() => {
            this.btnState = "done";
            setTimeout(() => {
              this.$root.$emit("reloadData");
              this.$router.back();
            }, 500);
          });
    },
  },
  mounted() {
    this.$root.$on('reloadData', () => {
      this.getMailbox()
      this.getGroupsAvail()
    })
  },
};
</script>