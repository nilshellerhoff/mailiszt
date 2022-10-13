<template>
  <v-container class="ma-0 pa-1 pa-sm-4" fluid>
    <v-row no-gutters>
      <v-col class="d-none d-sm-flex">
        <v-text-field
          v-model="search"
          prepend-icon="mdi-magnify"
          clearable
          label="Search"
          hide-details
          autocomplete="false"
        ></v-text-field>
      </v-col>
      <v-col cols=auto class="py-2 pl-2">
        <!-- search button (only on xs) -->
        <v-btn
          class="mx-1 d-sm-none pa-2 pa-sm-4"
          :color="mobileSearchVisible ? 'grey lighten-2' : 'grey lighten-4'"
          @click="mobileSearchVisible = !mobileSearchVisible"
        ><v-icon>mdi-magnify</v-icon>search
        </v-btn>
        <!-- add new button -->
        <v-btn
          class="mx-1 pa-2 pa-sm-4"
          color="primary"
          to="/mailboxes/add"
          >
          <v-icon class="mr-2">mdi-plus</v-icon>
          add new
        </v-btn>
      </v-col>
    </v-row>

    <!-- search field on mobile -->
    <v-row no-gutters v-show="mobileSearchVisible" class="d-sm-none">
      <v-col>
        <v-text-field
          v-model="search"
          prepend-icon="mdi-magnify"
          clearable
          label="Search"
          hide-details
          autocomplete="false"
        ></v-text-field>
      </v-col>
    </v-row>

    <!-- table -->
    <v-data-table :headers="headers" :items="mailboxes" :search="search" :options="{ itemsPerPage: -1 }">
      <!-- edit link -->
      <template v-slot:[`item.actions`]="{ item }">
        <router-link :to="'/mailboxes/edit/' + item.i_mailbox">
          <v-btn small class="mr-2">Edit</v-btn>
        </router-link>
        <v-btn small color="error" @click="deleteMailbox(item.i_mailbox, item.s_name)">delete</v-btn>
      </template>
    </v-data-table>
  </v-container>
</template>

<style scoped>
  a {
    text-decoration: none;
  }
</style>

<script>
export default {
  name: "Home",
  data: () => ({
    search: "",
    mailboxes: [],
    headers: [
      { text: "Name", value: "s_name" },
      { text: "E-Mail address", value: "s_address" },
      { text: "", value: "actions", sortable: false, align: "right" },
    ],
    mobileSearchVisible: false,
  }),
  methods: {
    getMailboxes() {
      this.$api.get("/mailbox").then((response) => {
        this.mailboxes = response.data.payload;
      });
    },
    deleteMailbox(i_mailbox, name) {
      this.$root
        .$confirm("Delete group", `Are you sure you want to delete the mailinglist ${name}?`, {
          color: "red",
        })
        .then((confirm) => {
          if (confirm) {
            this.$api.delete(`/mailbox/${i_mailbox}`).then(() => {
              this.$root.$emit("reloadData");
            });
          }
        });
    },
  },
  mounted() {
    this.$root.$on("reloadData", () => {
      this.getMailboxes();
    });
  },
};
</script>
