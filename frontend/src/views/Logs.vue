<template>
  <v-container class="ma-0 pa-1 pa-sm-4" fluid>

    <!-- Search Field (not working for now as apiside filtering not implemented yet) -->
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
      <v-col cols="auto" class="py-2 pl-2">
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
    <v-data-table
      :headers="headers"
      :items="filteredLogs"
      :page.sync="page"
      :items-per-page.sync="itemsPerPage"
      @pagination="loadData"
      :server-items-length="serverItemsLength"
    >
    </v-data-table>
  </v-container>
</template>

<script>
export default {
  name: "Logs",
  data: () => {
    return {
        search: "",
        headers: [
            { text: "Level", value: "s_level" },
            { text: "Message", value: "s_message" },
            { text: "Time", value: "d_inserted" },
        ],
        logs: [],
        mobileSearchVisible: false,
        page: 1,
        itemsPerPage: 10,
        serverItemsLength: -1,
    }
  },
  computed: {
    filteredLogs: function () {
      return this.logs.filter((log) => {
        let filterFields =
          (log.s_level || "") + " " +
          (log.s_message || "");
        let search = (this.search || "").toLowerCase();
        return filterFields.toLowerCase().includes(search);
      });
    },
  },
  methods: {
    loadServerItemsLength() {
      // this is kinda hacky, we request the number of items from the server by requesting a non existing field
      this.$api.get("/log?fields=a").then((response) => {
        this.serverItemsLength = response.data.length;
      });
    },
    loadData() {
        let $limit = this.itemsPerPage
        let $offset = (this.page - 1) * this.itemsPerPage
        this.$api.get(`/log?limit=${$limit}&offset=${$offset}`).then(response => this.logs = response.data)
    },
    logEvent(event) {
      console.log(event)
    }
  },
  mounted() {
    this.$root.$on("reloadData", () => {
      this.loadServerItemsLength();
      this.loadData();
    });
  },
};
</script>
