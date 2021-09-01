<template>
  <div>
    <div class="login-background"></div>
    <v-dialog value="true" max-width="400" persistent>
      <v-card max-width="400" class="pa-4 mx">
        <v-card-title> Login </v-card-title>
        <v-form @submit.prevent="login">
          <v-text-field
            v-model="username"
            label="Username"
            prepend-icon="mdi-account"
            :error="loginError"
          ></v-text-field>
          <v-text-field
            style="z-index: 5000"
            v-model="password"
            :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
            :type="showPassword ? 'text' : 'password'"
            label="Password"
            @click:append="showPassword = !showPassword"
            prepend-icon="mdi-lock"
            :error="loginError"
            :messages="[loginError ? 'Wrong username or password' : '']"
          ></v-text-field>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" type="submit" :loading="btnLoading">
              <v-icon>mdi-login</v-icon>
              Log in
            </v-btn>
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
export default {
  name: "Login",
  data: () => ({
    username: "",
    password: "",
    showPassword: false,
    btnLoading: false,
    loginError: false,
  }),
  methods: {
    login() {
      this.btnLoading = true;
      this.$root.$refs.App.login(this.username, this.password).then(
        (status) => {
          if (status) {
            this.$router.push("/");
          } else {
            this.loginError = true;
            this.password = "";
            this.btnLoading = false;
          }
        }
      );
    },
  },
};
</script>
