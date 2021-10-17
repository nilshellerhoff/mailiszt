<template>
    <MailPopup
      :popupTitle='`Message "${mail.s_subject}"`'
      :mail="mail"
      :recipients="recipients"
    >
    </MailPopup>
</template>

<script>
import MailPopup from '@/components/MailPopup.vue'

export default {
  name: "ViewMail",
  data: function () {
    return {
      mailId: this.$route.params.id,
      mail: {},
      recipients: [],
    }
  },
  components: {
      MailPopup
  },
  methods: {
    getMail() {
      this.$api.get(`/mail/${this.mailId}`).then((response) => {
        this.mail = response.data;
      });
    },
    getRecipients() {
      this.$api.get(`/mail/${this.mailId}/recipient`).then((response) => {
        this.recipients = response.data;
      });
    }
  },
  mounted() {
    this.getMail();
    this.getRecipients();
    this.$root.$on('reloadData', () => {
      this.getMail()
      this.getRecipients()
    })
  },
};
</script>