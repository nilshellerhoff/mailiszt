<template>
  <div>
    <div class="dialog-background" :style="dialog ? 'display:block' : 'display:none'"></div>
      <v-dialog v-model="dialog" max-width="400" persistent hide-overlay>
        <v-card>
          <v-toolbar dark color="secondary">
            <v-toolbar-title>Login</v-toolbar-title>
          </v-toolbar>
          <v-card class="pa-4">
            <v-form @submit.prevent="login">
              <v-text-field
                v-model="username"
                label="Username"
                prepend-icon="mdi-account"
              ></v-text-field>
              <v-text-field
                v-model="password"
                :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                :type="showPassword ? 'text' : 'password'"
                label="Password"
                @click:append="showPassword = !showPassword"
                prepend-icon="mdi-lock"
              ></v-text-field>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" type="submit">Login</v-btn>
              </v-card-actions>
            </v-form>
          </v-card>
        </v-card>
      </v-dialog>
    </div>
</template>

<style scoped>
.dialog-background {
  position: absolute;
  top:0;left:0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,.8);
  backdrop-filter: blur(10px);
  z-index: 100;
}
</style>

<script>
export default {
  name: "Login",
  props: ["dialog"],
  data: () => ({
    username: "",
    password: "",
    showPassword: false,
  }),
  methods: {
    login() {
      this.dialog = false;
    }
  }
};
</script>
