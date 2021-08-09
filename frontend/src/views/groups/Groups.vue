<template>
  <v-container class="m-10">
    <v-row no-gutters>
      <v-col sm="3" cols="6">
        <h1>Groups</h1>
      </v-col>
      <v-col sm="3" cols="6" align=right order="1" order-sm="2" class=pa-2>
        <router-link to="/groups/add">
          <v-btn color="primary">Add new</v-btn>
        </router-link>
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
          <v-icon small>mdi-pencil</v-icon>
        </router-link>
        <v-icon @click="deleteGroup(item.i_group)" small>mdi-delete</v-icon>
      </template>
    </v-data-table>
  </v-container>
</template>

<script>
export default {
  name: "Home",
  data: () => ({
    search: "",
    groups: [],
    headers: [
      { text: "Name", value: "s_name" },
      { text: "Actions", value: "actions" },
    ],
  }),
  methods: {
    getGroups() {
      this.$api.get("/group").then((response) => {
        this.groups = response.data;
      });
    },
    deleteGroup(i_group) {
      this.$api.delete(`/group/${i_group}`).then(() => {
        this.$root.$emit('reloadData')
      })
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
