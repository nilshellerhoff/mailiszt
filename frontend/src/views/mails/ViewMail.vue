<template>
    <MailPopup
      :popupTitle='`Message "${mail.s_subject}"`'
      :mail="mail"
      :recipients="mail.recipients"
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
  },
  mounted() {
    this.$root.$on('reloadData', () => {
      this.getMail()
    })
  },
};
</script>