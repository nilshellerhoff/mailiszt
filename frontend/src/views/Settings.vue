<template>
  <v-container fluid class="ma-0 pa-1 pa-sm-4">
    <span class="text-h3">Mailiszt version {{ versionNumber }}</span><br><br>

    <span class="text-h6">Change password</span>
    <change-password></change-password>


    <span class="text-h6">Cron-Job</span><br>
    <cronjob></cronjob>


    <span class="text-h6">DB access</span><br>
    <span class="text-subtitle-1">Access the DB directly via a SQL prompt. URL is <code>&lt;host&gt;/dbadmin</code>.
</span>
    <enable-dbadmin></enable-dbadmin>
  </v-container>
</template>

<script>
import ChangePassword from '@/components/Settings/ChangePassword.vue'
import EnableDbadmin from '@/components/Settings/EnableDbadmin.vue'
import Cronjob from '@/components/Settings/Cronjob.vue'

export default {
  name: "Settings",
  components: {
    ChangePassword,
    EnableDbadmin,
    Cronjob,
  },
  data: () => ({
    versionNumber: ''
  }),
  mounted() {
    this.$root.$on('reloadData', () => {
      this.$api.get(`/setting/version_number`).then(response => {
        this.versionNumber = response.data
      })
    })
  }
};
</script>
