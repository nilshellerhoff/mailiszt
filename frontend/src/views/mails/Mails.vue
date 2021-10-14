<template>
  <v-container class="m-10">
    <v-row no-gutters>
      <v-col sm="3" cols="6">
        <h1>Mails</h1>
      </v-col>
      <v-col sm="3" cols="6" align=right order="1" order-sm="2" class=pa-2>
      </v-col>
      <v-col sm="6" cols="12" order="2" order-sm="1" class=mt-n1>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search"
          single-line
          hide-details
        ></v-text-field>
      </v-col>
    </v-row>
    <v-data-table :headers="headers" :items="mails" :search="search">
      <!-- details button -->
      <template v-slot:[`item.actions`]="{ item }">
        <router-link :to="'/mails/' + item.i_mail">
          <v-btn>Details</v-btn>
        </router-link>
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
    mails: [],
    headers: [
      { text: "List", value: "s_tomail" },
      { text: "Subject", value: "s_subject" },
      { text: "", value: "actions"}
    ],
  }),
  methods: {
    getMails() {
      this.$api.get("/mail")
        .then((response) => {this.mails = response.data;});
    },
  },
  mounted() {
    this.getMails();
    this.$root.$on("reloadData", () => {
      this.getMails();
    });
  },
};
</script>
