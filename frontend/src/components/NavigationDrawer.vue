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
            <span v-for="link in links" :key="link.title">

              <!-- if we have no sublinks -->
              <template v-if="!link.sublinks">
                <router-link :to="link.url">
                  <v-list-item link>
                      <v-list-item-icon>
                        <v-icon>{{ link.icon }}</v-icon>
                      </v-list-item-icon>
                      <v-list-item-content>
                        <v-list-item-title>{{link.title}}</v-list-item-title>
                      </v-list-item-content>
                  </v-list-item>
                </router-link>
              </template>

              <!-- if we have sublinks -->
              <template v-if="link.sublinks">
                <v-list-group :prepend-icon="link.icon">

                  <template v-slot:activator>
                    <v-list-item-content>
                      <v-list-item-title>{{link.title}}</v-list-item-title>
                    </v-list-item-content>
                  </template>

                  <v-list-item v-for="sublink in link.sublinks" :key="sublink">
                    <v-list-item-content>
                      <v-list-item-title>{{sublink.title}}</v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                  
                </v-list-group>
              </template>

            </span>
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
      loadData() {
        this.$api.get(`mailbox?fields=s_name`)
        .then(result => {
          // console.log(this.links.filter(m => m.title == "Mailboxes")[0]);
          this.links.filter(m => m.title == "Mails")[0].sublinks = result.data.map(r => ({title: r.s_name}))
        })
      },
      logout() {
        this.$root.$refs.App.logout()
        this.$router.push('/login')
      }
    },
    mounted() {
      this.$root.$on("reloadData", () => {
        this.loadData();
      });
    },
  }
</script>