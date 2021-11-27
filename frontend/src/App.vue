<template>
  <v-app id="app">
    <NavigationDrawer :links="links" v-show="$route.name == 'Login' ? false : true">
      <div style="position: absolute; top: 64px; overflow-y: scroll; width: 100vw">
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
        url: "/mails",
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
    getUserInfo() {
      if (this.loggedIn()) {
        // populate the userinfo object
        // if we get a 403 delete the local login data and redirect to login page
        this.$api.put("users/current/", { accessToken: this.accessToken })
        .then(response => {
          this.userInfo = response.data
        })
        .catch(() => { this.logoutLocal() })
      } else { this.logoutLocal() }
    },

    // generic functions for login managment
    login(username, password) {
      // request a login at the server and store the accessToken as cookie and global var
      return this.$api
        .put("users/current/login", { username: username, password: password })
        .catch((response) => {return Promise.reject(response.status)})
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
    logoutLocal() {
      // delete the accessToken from storage and cookie
      this.accessToken = null;
      this.$api.defaults.headers["Authorization"] = null;
      this.$cookies.set("accessToken", "");
      this.$router.push({ name: "Login" })
    },
    logout() {
      // delete the accessToken from storage and cookie
      return this.$api
        .put("users/current/logout", { accessToken: this.accessToken })
        .then(() => {
          this.logoutLocal()
          return true
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