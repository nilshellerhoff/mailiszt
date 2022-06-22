<template>
    <div>
        <v-app-bar fixed
          color="secondary"
          dark
          style="z-index: 10"
        >
        <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
        <v-toolbar-title class="font-weight-bold">{{$route.name}}</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-title class="ma-4">{{$root.$refs.App.userInfo.s_username}}</v-toolbar-title>
        <v-btn @click="logout">
          <v-icon left>mdi-logout</v-icon>log out
        </v-btn>
        </v-app-bar>

        <v-navigation-drawer
          v-model="drawer"
          fixed
          temporary
          style="top: 64px; height: calc(100vh - 64px)"
        >


        <v-list
          nav
        >
          <v-list-item-group
          v-model="group"
          active-class="blue--text text--accent-4"
          >
            <router-link v-for="link in links" :key="link.url" :to=link.url>
              <v-list-item link>
                  <v-list-item-icon>
                    <v-icon>{{ link.icon }}</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title>{{link.title}}</v-list-item-title>
                  </v-list-item-content>
              </v-list-item>
            </router-link>
          </v-list-item-group>
  
        </v-list>
        </v-navigation-drawer>
        <slot></slot>
        </div>
</template>

<style scoped>
  a {
    text-decoration: none;
  }
</style>

<script>
  export default {
    name: "NavigationDrawer",
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
          title: "Inactive members",
          url: "/members/inactive",
          icon: "mdi-account-off",
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
        { title: "Logs",
          url: "/logs",
          icon: "mdi-alert"
        }
      ],
      drawer: false,
      // group: null,
    }),
    watch: {
      group () {
        this.drawer = false
      },
    },
    computed: {
        mobile() {
            return this.$vuetify.breakpoint.sm;
        },
        group: {
          get() {
            let currPath = this.$route.path
            let links = this.links.map(l => l.url)
            return links.indexOf(currPath)
          },
          set (newGroup) {
            return newGroup
          }
        }
    },
    methods: {
      logout() {
        this.$root.$refs.App.logout()
        this.$router.push('/login')
      }
    }
  }
</script>