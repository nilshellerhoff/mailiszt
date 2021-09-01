<template>
    <div>
        <v-app-bar
        color="secondary"
        dark
        >
        <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
        <v-spacer></v-spacer>
        <v-toolbar-title class="ma-4">{{$root.$refs.App.userInfo.s_username}}</v-toolbar-title>
        <v-btn @click="logout">
          <v-icon>mdi-logout</v-icon>log out
        </v-btn>
        </v-app-bar>

        <v-navigation-drawer
          v-model="drawer"
          absolute
          temporary
        >
        <v-list
          nav
        >
          <v-list-item>
            <v-list-item-content>
              <v-list-item-title class="text-h6">
                Application
              </v-list-item-title>
              <v-list-item-subtitle>
                subtext
              </v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>

          <v-divider></v-divider>

          <v-list-item-group
          v-model="group"
          active-class="deep-purple--text text--accent-4"
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
    props: [
      "links"
    ],
    data: () => ({
      drawer: false,
      group: null,
    }),

    watch: {
      group () {
        this.drawer = false
      },
    },
    computed: {
        mobile() {
            return this.$vuetify.breakpoint.sm;
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