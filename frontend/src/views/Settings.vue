<template>
  <v-container fluid class="ma-0 pa-1 pa-sm-4">
    <h2>Mailiszt version {{ versionNumber }}</h2>
    <h2>Change password</h2>
    <change-password></change-password>


    <h2>Cron-Job</h2>
    <v-row>
      <v-col cols="4">point your cron-job to the following url:</v-col>
      <v-col cols="5"><v-text-field disabled dense single-line v-model="cronUrl"></v-text-field></v-col>
      <v-col cols="3">
        <v-btn @click="urlCopy">
          <v-icon left>mdi-content-copy</v-icon>
          copy
        </v-btn>
      </v-col>
    </v-row>

    <h3>DB access</h3>
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
    versionNumber: 'blub'
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
