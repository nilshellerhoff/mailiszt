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
  name: "AddMailbox",
  data: function () {
    return {
      mailbox: {},
      btnState: "ready",
    };
  },
  components: {
    MailboxPopup,
  },
  methods: {
    async saveMailbox() {
      this.btnState = "loading";
      this.$api.put(`/mailbox/add`, this.mailbox).then(() => {
        this.btnState = "done";
        setTimeout(() => {
          this.$root.$emit("reloadData");
          this.$router.back();
        }, 500);
      });
    },
  },
};
</script>
