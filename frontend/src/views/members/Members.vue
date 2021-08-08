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
      <!-- show active status as checkbox -->
      <template v-slot:[`item.b_active`]="{ item }">
        <v-checkbox
          v-model="item.b_active"
          :true-value="1"
          :false-value="0"
        ></v-checkbox>
      </template>

      <!-- edit link -->
      <template v-slot:[`item.actions`]="{ item }">
        <router-link :to="'/members/edit/' + item.i_member">
          <v-icon small>mdi-pencil</v-icon>
        </router-link>
        <v-icon @click="deleteMember(item.i_member)" small>mdi-delete</v-icon>
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
      { text: "First name", value: "s_name1" },
      { text: "Second name", value: "s_name2" },
      { text: "Email", value: "s_email" },
      { text: "Active", value: "b_active" },
      { text: "Actions", value: "actions" },
    ],
  }),
  methods: {
    getMembers() {
      this.$api.get("/member").then((response) => {
        this.members = response.data;
      });
    },
    deleteMember(i_member) {
      console.log(`Deleting ${i_member}`)
      this.$api.delete(`/member/${i_member}`).then(() => {
        console.log(`Deleted ${i_member}`)
        this.$root.$emit('reloadData')
      })
    }
  },
  mounted() {
    this.getMembers();
    this.$root.$on("reloadData", () => {
      this.getMembers();
    });
  },
};
</script>
