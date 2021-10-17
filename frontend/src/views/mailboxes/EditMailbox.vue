<template>
  <MailboxPopup
    @save="saveMailbox()"
    :popupTitle="`Add mailbox`"
    :btnState="btnState"
    :mailbox="mailbox"
    :groupsAvail="groupsAvail"
    :groups="groups"
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
      groups: {
        id: 1631607982497,
        comparisonOperator: "=",
        value: 9,
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
    getMailbox() {
      this.$api.get(`/mailbox/${this.mailboxId}`).then((response) => {
        this.mailbox = response.data;
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
    getGroups() {
      this.$api.get(`/mailbox/${this.mailboxId}/groups`).then((response) => {
        this.groups = response.data;
      });
    },
    async saveMailbox() {
      this.btnState = "loading";
      this.$api.put(`/mailbox/${this.mailboxId}`, this.mailbox).then(() => {
        this.$api
          .put(`/mailbox/${this.mailboxId}/groups`, this.groups)
          .then(() => {
            this.btnState = "done";
            setTimeout(() => {
              this.$root.$emit("reloadData");
              this.$router.back();
            }, 500);
          });
      });
    },
  },
  mounted() {
    this.getMailbox();
    this.getGroupsAvail();
    this.getGroups();
    this.$root.$on('reloadData', () => {
      this.getMailbox()
      this.getGroupsAvail()
      this.getGroups()
    })
  },
};
</script>