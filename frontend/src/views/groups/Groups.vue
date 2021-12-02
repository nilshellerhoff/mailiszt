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
          to="/groups/add"
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
    <v-data-table :headers="headers" :items="groups" :search="search" :options="{ itemsPerPage: -1 }">
      <!-- edit link -->
      <template v-slot:[`item.actions`]="{ item }">
        <router-link :to="'/groups/edit/' + item.i_group">
          <v-btn small class="mr-2">Edit</v-btn>
        </router-link>
        <v-btn small color="error" @click="deleteGroup(item.i_group, item.s_name)">Delete</v-btn>
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
    groups: [],
    headers: [
      { text: "Name", value: "s_name" },
      { text: "", value: "actions", sortable: false, align: "right" },
    ],
    mobileSearchVisible: false,
  }),
  methods: {
    getGroups() {
      this.$api.get("/group").then((response) => {
        this.groups = response.data;
      });
    },
    deleteGroup(i_group, name) {
      this.$root.$confirm('Delete group', `Are you sure you want to delete the group ${name}?`, { color: 'red' })
        .then((confirm) => {
          if (confirm) {
            this.$api.delete(`/group/${i_group}`).then(() => {
              this.$root.$emit('reloadData')
            })
          }
        });
    }
  },
  mounted() {
    this.getGroups();
    this.$root.$on("reloadData", () => {
      this.getGroups();
    });
  },
};
</script>
