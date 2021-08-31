<template>
  <div>
    <!-- <div class="dialog-background" :style="dialog ? 'display:block' : 'display:none'"></div> -->
    <div class="login-background"></div>
    <v-dialog value="true" max-width=400>
      <v-card max-width="400" class="pa-4 mx">
        <v-card-title> Login </v-card-title>
        <v-form @submit.prevent="login">
          <v-text-field
            v-model="username"
            label="Username"
            prepend-icon="mdi-account"
          ></v-text-field>
          <v-text-field
            style="z-index: 5000"
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
  background-color: rgba(0,0,0,.8);
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
  }),
  methods: {
    login() {
      
    },
  },
};
</script>
