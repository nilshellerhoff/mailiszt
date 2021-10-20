<template>
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
    <v-col cols="3">
      <ActionButton
        ref="button"
        label="change password"
        @click="updatePassword"
      ></ActionButton>
    </v-col>
  </v-row>
</template>

<script>
import PasswordInput from "@/components/PasswordInput.vue";
import ActionButton from "@/components/ActionButton.vue"

export default {
  name: "ChangePassword",
  components: {
    PasswordInput,
    ActionButton
  },
  data: () => ({
    oldPassword: "",
    newPassword1: "",
    newPassword2: "",
    oldPasswordError: false,
    newPasswordError: false,
  }),
  methods: {
    updatePassword() {
      this.$refs.button.loading(true)
      this.$api
        .put("/users/current/password", {
          accessToken: this.$root.$refs.App.accessToken,
          oldPassword: this.$refs.oldPassword.password,
          newPassword1: this.$refs.newPassword1.password,
          newPassword2: this.$refs.newPassword2.password,
        })
        .then(() => {
          this.$refs.button.loading(false)
          this.$refs.button.success()
        })
        .catch((error) => {
          this.$refs.button.loading(false)
          this.$refs.button.error()
          if (error.response.status == 400) {
            this.newPasswordError = true;
          }
          if (error.response.status == 403) {
            this.oldPasswordError = true;
          }
        });
    },
    resetForm() {
      this.oldPasswordError = false;
      this.newPasswordError = false;
      this.$refs.oldPassword.password = "";
      this.$refs.newPassword1.password = "";
      this.$refs.newPassword2.password = "";
    },
  },
};
</script>