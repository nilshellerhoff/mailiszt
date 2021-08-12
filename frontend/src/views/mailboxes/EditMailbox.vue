<template>
  <MailboxPopup
    @save="saveMailbox()"
    :popupTitle="`Add mailbox`"
    :btnState="btnState"
    :mailbox="mailbox"
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
    async saveMailbox() {
      this.btnState = "loading";
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
    this.getMailbox();
  },
};
</script>