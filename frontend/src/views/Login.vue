<template>
  <div>
    <div class="login-background"></div>
    <v-dialog value="true" max-width="400" persistent :hide-overlay="true">
      <v-card max-width="400" class="pa-4 mx">
        <v-card-title> Login </v-card-title>
        <v-form @submit.prevent="login">
          <v-text-field
            v-model="username"
            label="Username"
            prepend-icon="mdi-account"
            :error="loginError"
            autocomplete="off"
          ></v-text-field>
          <PasswordInput
            ref="passwordInput"
            label="Password"
            :password="password"
            :error="loginError"
            :message="loginError ? 'Wrong username or password' : ''"
            :prepend-icon="'mdi-lock'"
          ></PasswordInput>
          <v-card-actions>
            <v-spacer></v-spacer>
            <ActionButton
              ref="loginButton"
              label="login"
              type="submit"
            ></ActionButton>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
  </div>
</template>


<style>
.login-background {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.8);
  background-color: #444;
}
</style>

<script>
import ActionButton from "@/components/ActionButton.vue";
import PasswordInput from "@/components/PasswordInput.vue";

export default {
  name: "Login",
  components: { PasswordInput, ActionButton },
  data: () => ({
    username: "",
    password: "",
    showPassword: false,
    loading: false,
    loginError: false,
  }),
  methods: {
    login() {
      this.password = this.$refs.passwordInput.password;
      this.$refs.loginButton.loading();
      this.$root.$refs.App.login(this.username, this.password)
      .then(() => {
          this.$router.push("/");
          this.$refs.loginButton.success();
        }
      ).catch(() => {
          this.password = ""
          this.$refs.passwordInput.password = this.password
          this.$refs.loginButton.error();
          this.loginError = true;
      })
    },
  },
  mounted() {
    // if logged in, redirect to home
    if (this.$root.$refs.App.loggedIn()) {
      this.$router.push("/");
    }
  },
};
</script>
