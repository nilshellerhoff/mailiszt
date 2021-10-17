<template>
  <v-container class="m-10">
    <v-row no-gutters>
      <v-col sm="3" cols="6">
        <h1>Mailboxes</h1>
      </v-col>
      <v-col sm="3" cols="6" align="right" order="1" order-sm="2" class="pa-2">
        <router-link to="/mailboxes/add">
          <v-btn color="primary">Add new</v-btn>
        </router-link>
      </v-col>
      <v-col sm="6" cols="12" order="2" order-sm="1" class="mt-n1">
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search"
          single-line
          hide-details
        ></v-text-field>
      </v-col>
    </v-row>
    <v-data-table :headers="headers" :items="mailboxes" :search="search">
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
  }),
  methods: {
    getMailboxes() {
      this.$api.get("/mailbox").then((response) => {
        this.mailboxes = response.data;
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
    this.getMailboxes();
    this.$root.$on("reloadData", () => {
      this.getMailboxes();
    });
  },
};
</script>
