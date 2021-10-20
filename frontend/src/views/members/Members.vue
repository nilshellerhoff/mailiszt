<template>
  <v-container class="m-10">
    <v-row no-gutters>
      <v-col sm="3" cols="6">
        <h1>Members</h1>
      </v-col>
      <v-col sm="3" cols="6" align=right order="1" order-sm="2" class=pa-2>
        <router-link to="/members/add">
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
    <v-data-table :headers="headers" :items="members" :search="search">
      <!-- name -->
      <template v-slot:[`item.name`]="{ item }">
        {{ item.s_name1 }} {{ item.s_name2 }}
      </template>

      <!-- groups --> 
      <template v-slot:[`item.groups`]="{ item }">
        <v-chip-group column style="white-space: nowrap">
          <div v-for="g in item.groups" :key="g.i_group">
            <v-chip :to="`/groups/edit/${g.i_group}`" class="mr-1">{{ g.s_name }}</v-chip>
          </div>
        </v-chip-group>
      </template>

      <!-- edit link -->
      <template v-slot:[`item.actions`]="{ item }">
        <div style="white-space: nowrap">
          <v-btn small class="mr-1" :to="'/members/edit/' + item.i_member">Edit</v-btn>
          <v-btn small color="error" @click="deleteMember(item.i_member, item.s_name1 + ' ' + item.s_name2)">Delete</v-btn>
        </div>
      </template>
    </v-data-table>
  </v-container>
</template>

<script>
export default {
  name: "Home",
  data: () => ({
    search: "",
    members: [],
    headers: [
      { text: "Name", value: "name" },
      // { text: "Second name", value: "s_name2" },
      { text: "Groups", value: "groups" },
      { text: "", value: "actions", sortable: false, align: "right"},
    ],
  }),
  methods: {
    getMembers() {
      this.$api.get("/member").then((response) => {
        this.members = response.data;
      })
    },
    deleteMember(i_member, name) {
      this.$root.$confirm('Delete member', `Are you sure you want to delete the member ${name}?`, { color: 'secondary' })
        .then((confirm) => {
          if (confirm) {
            // console.log(`Deleting ${i_member}`)
            this.$api.delete(`/member/${i_member}`).then(() => {
              console.log(`Deleted ${i_member}`)
              this.$root.$emit('reloadData')
            })
          }
        });
    },
  },
  mounted() {
    this.getMembers();
    this.$root.$on("reloadData", () => {
      this.getMembers();
    });
  },
};
</script>
