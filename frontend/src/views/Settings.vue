<template>
  <div>
    <h1>Settings</h1>
    <h2>User</h2>
    <h3>Change password</h3>
    <v-row>
        <v-col cols="3">
          <PasswordInput
            ref="oldPassword"
            label="old password"
            :password="oldPassword"
            :error="oldPasswordError"
            :message="oldPasswordError ? 'wrong password' : ''"
          ></PasswordInput>
        </v-col>
        <v-col cols="3">
          <PasswordInput
            ref="newPassword1"
            label="new password"
            :password="newPassword1"
            :error="newPasswordError"
            :message="newPasswordError ? 'passwords do not match' : ''"
          ></PasswordInput>
        </v-col>
        <v-col cols="3">
          <PasswordInput
            ref="newPassword2"
            label="repeat password"
            :password="newPassword2"
            :error="newPasswordError"
            :message="newPasswordError ? 'passwords do not match' : ''"
          ></PasswordInput>
        </v-col>
    </v-row>
    <v-row>
      <v-col cols=3>
        <v-btn @click="updatePassword">change password!</v-btn>
      </v-col>
    </v-row>

    <h2>asdfsadf</h2>
    <h3>Cron-Job</h3>
    point your cron-job to the following url: 
  </div>
</template>

<script>
import PasswordInput from '@/components/PasswordInput.vue'

export default {
  name: "Settings",
  components: {
    PasswordInput
  },
  data: () => ({
    oldPassword: '',
    newPassword1: '',
    newPassword2: '',
    oldPasswordError: false,
    newPasswordError: false,
  }),
  methods: {
    updatePassword() {
      this.$api.put(
        '/users/current/password',
        {
          accessToken: this.$root.$refs.App.accessToken,
          oldPassword: this.$refs.oldPassword.password,
          newPassword1: this.$refs.newPassword1.password,
          newPassword2: this.$refs.newPassword2.password,
        }
      ).catch(error => {
        if (error.response.status == 400) {
          this.newPasswordError = true;
        } 
        if (error.response.status == 403) {
          this.oldPasswordError = true;
        }
      })
    },
    resetForm() {
      this.oldPasswordError = false;
      this.newPasswordError = false;
      this.$refs.oldPassword.password = '';
      this.$refs.newPassword1.password = '';
      this.$refs.newPassword2.password = '';
    }
  }
};
</script>
