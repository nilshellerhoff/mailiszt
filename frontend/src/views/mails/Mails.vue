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
    <v-data-table :headers="headers" :items="mails" :search="search">
      <!-- sent date -->
      <template v-slot:[`item.date`]="{ item }">
        {{ getDate(item.d_sent) }}
      </template>

      <!-- details button -->
      <template v-slot:[`item.actions`]="{ item }">
        <v-btn :to="'/mails/' + item.i_mail">View</v-btn>
      </template>
    </v-data-table>
  </v-container>
</template>

<script>
import { formatDistance, parseISO } from 'date-fns'

export default {
  name: "Home",
  data: () => ({
    search: "",
    mails: [],
    headers: [
      { text: "List", value: "s_tomail" },
      { text: "Sent", value: "date" },
      { text: "Subject", value: "s_subject" },
      { text: "Recipients", value: "num_recipients"},
      { text: "", value: "actions", sortable: false, align: "right"}
    ],
    mobileSearchVisible: false,
  }),
  methods: {
    getMails() {
      this.$api.get("/mail?fields=i_mail,s_tomail,s_subject,num_recipients,d_sent")
        .then((response) => {this.mails = response.data;});
    },
    getDate(isodate) {
      return formatDistance(parseISO(isodate), new Date(), { addSuffix: true })

    }
  },
  mounted() {
    this.$root.$on("reloadData", () => {
      this.getMails();
    });
  },
};
</script>
