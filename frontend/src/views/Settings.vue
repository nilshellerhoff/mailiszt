<template>
  <v-container fluid class="ma-0 pa-1 pa-sm-4">
    <span class="text-h3">Mailiszt version {{ versionNumber }}</span><br><br>

    <span class="text-h6">Change password</span>
    <change-password></change-password>


    <span class="text-h6">Cron-Job</span><br>
    <span class="text-subtitle-1">Call the following URL periodically in order check for new mails</span>
    <v-row>
      <v-col cols="5"><v-text-field disabled dense single-line v-model="cronUrl"></v-text-field></v-col>
      <v-col cols="3">
        <v-btn @click="urlCopy">
          <v-icon left>mdi-content-copy</v-icon>
          copy
        </v-btn>
      </v-col>
    </v-row>

    <span class="text-h6">DB access</span><br>
    <span class="text-subtitle-1">Access the DB directly via a SQL prompt. URL is <code>&lt;host&gt;/dbadmin</code>.
</span>
    <enable-dbadmin></enable-dbadmin>
  </v-container>
</template>

<script>
import copy from 'copy-to-clipboard'
import ChangePassword from '@/components/Settings/ChangePassword.vue'
import EnableDbadmin from '@/components/Settings/EnableDbadmin.vue'

export default {
  name: "Settings",
  components: {
    ChangePassword,
    EnableDbadmin
  },
  data: () => ({
    cronUrl: process.env.VUE_APP_BASE_URL + '/mailbox/forward',
    versionNumber: ''
  }),
  methods: {
    urlCopy() {
      copy(this.cronUrl);
    },
  },
  mounted() {
    this.$root.$on('reloadData', () => {
      this.$api.get(`/setting/version_number`).then(response => {
        this.versionNumber = response.data
      })
    })
  }
};
</script>
