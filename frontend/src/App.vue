<template>
  <v-app id="app">
    <NavigationDrawer :links=links>
      <v-container
        fluid
        class="pa-0 ma-0"
        style="height: calc(100vh - 64px); overflow: scroll;">
        <v-container
          style="max-width: 1000px; width: 100%;"
        >
          <router-view/>
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
import NavigationDrawer from '@/components/NavigationDrawer.vue'

export default {
  name: 'App',
  components: {
    NavigationDrawer,
  },
  computed: {
    contentWidth () {
      if (this.$vuetify.breakpoint.smAndDown) {
        return '100%'
      } else {
        return '400'
      }
    }
  },
  data: () => ({
    loggedIn: false,
    links: [
      {
        title: "Home",
        url: "/",
        icon: 'mdi-home'
      },
      {
        title: "About",
        url: "/about"
      },
      {
        title: "Members",
        url: "/members",
        icon: 'mdi-account'
      },
      {
        title: "Groups",
        url: '/groups',
        icon: 'mdi-account-group'
      },
      {
        title: "Mailboxes",
        url: '/mailboxes',
        icon: 'mdi-email'
      },
      {
        title: "Settings",
        url: '/settings',
        icon: 'mdi-cog'
      },
    ]
  }),
  mounted() {
    // if not loggedin, redirect to login screen
    if (!this.loggedIn) {
      this.$router.push({name: 'Login'})
    }
  }
}
</script>
