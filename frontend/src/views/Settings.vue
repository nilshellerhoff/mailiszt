<template>
  <div>
    <h1>Settings</h1>
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
  </div>
</template>

<script>
import copy from 'copy-to-clipboard'
import ChangePassword from '@/components/Settings/ChangePassword.vue'

export default {
  name: "Settings",
  components: {
    ChangePassword
  },
  data: () => ({
    cronUrl: process.env.VUE_APP_BASE_URL + '/mailbox/forward',
  }),
  methods: {
    urlCopy() {
      copy(this.cronUrl);
    },
  }
};
</script>
