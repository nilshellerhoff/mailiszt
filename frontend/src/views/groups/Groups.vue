<template>
  <v-container class="m-10">
    <v-row no-gutters>
      <v-col sm="3" cols="6">
        <h1>Groups</h1>
      </v-col>
      <v-col sm="3" cols="6" align=right order="1" order-sm="2" class=pa-2>
        <v-btn color="primary" to="/groups/add">
          <v-icon class="mr-2">mdi-plus</v-icon>
          Add new
        </v-btn>
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
    <v-data-table :headers="headers" :items="groups" :search="search">
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
