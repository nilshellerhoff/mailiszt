<template>
  <v-app id="app">
    <NavigationDrawer v-show="$route.name == 'Login' ? false : true">
      <div style="position: absolute; top: 64px; width: 100vw;">
        <div style="max-width: 1000px; width: 100%; position: relative; margin: 0 auto">
          <router-view />
        </div>
      </div>
    </NavigationDrawer>
    <confirm ref="confirm"></confirm>
  </v-app>
</template>

<style lang="scss" scoped>
v-sheet {
  margin-left: auto;
  margin-right: auto;
}
</style>

<script>
import NavigationDrawer from "@/components/NavigationDrawer.vue";
import Confirm from '@/components/Confirm.vue'

export default {
  name: "App",
  components: {
    NavigationDrawer,
    Confirm
  },
  computed: {
    contentWidth() {
      if (this.$vuetify.breakpoint.smAndDown) {
        return "100%";
      } else {
        return "400";
      }
    },
  },
  data: () => ({
    accessToken: null,
    userInfo: {
      "i_user": "",
      "s_username": "",
      "s_role": "",
    }
  }),
  methods: {
    loggedIn() {
      // logged in is true if accessToken or the auth cookie is set
      let isLoggedIn = false;

      if (this.$cookies.get("auth") || this.accessToken) {
        isLoggedIn = true;

        // reload data (just in case)
        this.$root.$emit('reloadData');
      } else {
        // if we're not on the login page, redirect to login
        this.logoutLocal();
      }

      return isLoggedIn;
    },
    loginRedirect() {
      if (this.$route.path !== "/login") this.$router.push("/login")
    },
    getUserInfo() {
      if (this.loggedIn()) {
        // populate the userinfo object
        // if we, we have been logged out by the server -> logout client side as well
        this.$api
          .put("users/current/")
          .then((response) => {
            this.userInfo = response.data.payload[0];
          })
          .catch(() => {
            this.logoutLocal();
          });
      } else {
        this.logoutLocal();
      }
    },

    // generic functions for login managment
    login(username, password) {
      // request a login at the server and store the accessToken as cookie and global var
      return this.$api
        .put("users/current/login", { username: username, password: password })
        .catch((response) => {
          return Promise.reject(response.status);
        })
        .then((response) => {
          if (response.data) {
            this.accessToken = response.data.payload.token;
            this.$cookies.set("auth", response.data.payload.token);
            this.$api.defaults.headers["Authorization"] = response.data.payload.token;
            return true;
          } else {
            return false;
          }
        });
    },
    logoutLocal() {
      // delete the accessToken from storage and cookie
      this.accessToken = null;
      this.$cookies.set("auth", "");
      this.$api.defaults.headers["Authorization"] = null;
      this.loginRedirect()
    },
    logout() {
      // request logout at the server, then logout locally
      return this.$api.put("users/current/logout").then(() => {
        this.logoutLocal();
        return true;
      });
    },
  },
  created() {
    this.$root.$refs.App = this;
  },
  mounted() {
    this.getUserInfo();

    // make the confirm dialog accessible
    this.$root.$confirm = this.$refs.confirm.open
  },
  updated() {
    this.getUserInfo();
  },
};
</script>