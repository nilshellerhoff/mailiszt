<template>
  <v-app id="app">
    <NavigationDrawer :links="links">
      <v-container
        fluid
        class="pa-0 ma-0"
        style="height: calc(100vh - 64px); overflow: scroll"
      >
        <v-container style="max-width: 1000px; width: 100%">
          <router-view />
        </v-container>
      </v-container>
    </NavigationDrawer>
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

export default {
  name: "App",
  components: {
    NavigationDrawer,
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
    links: [
      {
        title: "Home",
        url: "/",
        icon: "mdi-home",
      },
      {
        title: "Members",
        url: "/members",
        icon: "mdi-account",
      },
      {
        title: "Groups",
        url: "/groups",
        icon: "mdi-account-group",
      },
      {
        title: "Mailboxes",
        url: "/mailboxes",
        icon: "mdi-email-multiple",
      },
      {
        title: "Mails",
        url: "/Mail",
        icon: "mdi-email",
      },
      {
        title: "Settings",
        url: "/settings",
        icon: "mdi-cog",
      },
    ],
    accessToken: null,
    userInfo: {
      "i_user": "",
      "s_username": "",
      "s_role": "",
    }
  }),
  methods: {
    readCookie() {
      const accessToken = this.$cookies.get("accessToken");
      if (accessToken) {
        this.accessToken = accessToken;
        this.$api.defaults.headers["Authorization"] = accessToken;
        this.$root.$emit('reloadData')
      }
    },
    loggedIn() {
      this.readCookie();
      return this.accessToken != null;
    },
    checkLogin() {
      // if not loggedin, redirect to login screen
      if (!this.loggedIn() && this.$route.name != "Login") {
        this.$router.push({ name: "Login" });
      }
    },
    getUserInfo() {
      if (this.loggedIn()) {
        // populate the userinfo object
        this.$api.put("users/current/", { accessToken: this.accessToken }).then(response => {
          this.userInfo = response.data
        })
      }
    },

    // generic functions for login managment
    login(username, password) {
      // request a login at the server and store the accessToken as cookie and global var
      return this.$api
        .put("users/current/login", { username: username, password: password })
        .then((response) => {
          if (response.data) {
            this.accessToken = response.data;
            this.$api.defaults.headers["Authorization"] = response.data;
            this.$cookies.set("accessToken", response.data);
            return true;
          } else {
            return false;
          }
        });
    },
    logout() {
      // delete the accessToken from storage and cookie
      return this.$api
        .put("users/current/logout", { accessToken: this.accessToken })
        .then(() => {
          this.accessToken = null;
          this.$api.defaults.headers["Authorization"] = null;
          this.$cookies.set("accessToken", "");
          this.$router.push({ name: "Login" })
          return true;
        });
    },
  },
  created() {
    this.$root.$refs.App = this;
  },
  mounted() {
    this.readCookie();
    this.checkLogin();
    this.getUserInfo();
  },
  updated() {
    this.checkLogin();
    this.getUserInfo();
  },
};
</script>
