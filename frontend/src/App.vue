<template>
  <v-app id="app">
    <NavigationDrawer :links=links>
      <v-sheet
        width=100%
        max-width=1200
      >
        <router-view/>
      </v-sheet>
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
    NavigationDrawer
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
  })
}
</script>
