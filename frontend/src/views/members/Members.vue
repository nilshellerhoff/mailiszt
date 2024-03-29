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
          :color="searchVisible ? 'grey lighten-2' : 'grey lighten-4'"
          @click="searchVisible = !searchVisible; groupsFilterVisible = false"
        ><v-icon>mdi-magnify</v-icon>search
        </v-btn>
        <!-- filter button -->
        <v-btn
          class="mx-1 pa-2 pa-sm-4"
          :color="groupsFilterVisible ? 'grey lighten-2' : 'grey lighten-4'"
          @click="groupsFilterVisible = !groupsFilterVisible; groupsFilter = []; searchVisible = false"
        ><v-icon>mdi-filter</v-icon>filter
        </v-btn>
        <!-- add new button -->
        <v-btn
          class="mx-1 pa-2 pa-sm-4"
          color="primary"
          :to="addUrl"
          >
          <v-icon class="mr-2">mdi-plus</v-icon>
          add new
        </v-btn>
      </v-col>
    </v-row>

    <!-- search field on mobile -->
    <v-row no-gutters v-show="searchVisible" class="d-sm-none">
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

    <!-- filter by group function -->
    <v-row no-gutters v-show="groupsFilterVisible">
      <v-col cols="auto" class="py-3 px-2">
        <div class="text-subtitle-2">
          Filter by group:
        </div>
      </v-col>
      <v-col>
        <v-chip-group column>
          <v-chip
            v-for="g in groups"
            :key="g.i_group"
            @click="switchGroupFilter(g.i_group)"
            :color="groupsFilter.includes(g.i_group) ? 'primary' : ''"
            >{{ g.s_name }}</v-chip
          >
        </v-chip-group>
      </v-col>
      <v-col cols="auto" class="py-1">
        <v-btn icon @click="groupsFilter = []" :disabled="groupsFilter.length == 0">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-col>
    </v-row>

    <!-- table -->
    <v-data-table :headers="headers" :items="filteredMembers" :options="{ itemsPerPage: -1 }">
      <!-- name -->
      <template v-slot:[`item.name`]="{ item }">
        {{ item.s_name1 }} {{ item.s_name2 }}
      </template>

      <!-- groups -->
      <template v-slot:[`item.groups`]="{ item }">
        <v-chip-group column style="white-space: nowrap">
          <div v-for="g in item.groups" :key="g.i_group">
            <v-chip :to="`/groups/edit/${g.i_group}`" class="mr-1">{{
              g.s_name
            }}</v-chip>
          </div>
        </v-chip-group>
      </template>

      <!-- edit link -->
      <template v-slot:[`item.actions`]="{ item }">
        <div style="white-space: nowrap">

          <!-- edit button -->
          <span>
            <v-btn small class="mr-1" :to="editUrl + item.i_member">
              Edit
            </v-btn>
          </span>
          <!-- activate button -->
          <span v-if="!active">
            <v-btn small class="mr-1" color="secondary" @click="switchMemberActive(item.i_member, true)">
              Activate
            </v-btn>
          </span>
          <!-- deactivate button -->
          <span v-if="active">
            <v-btn small class="mr-1" color="secondary" @click="switchMemberActive(item.i_member, false)">
              Deactivate
            </v-btn>
          </span>
          <!-- delete button -->
          <span v-if="!active">
            <v-btn small class="mr-1" color="error" @click="deleteMember(item.i_member)">
              Delete
            </v-btn>
          </span>

        </div>
      </template>

      <!-- footer -->
      <template v-slot:[`footer.prepend`]>
        <v-btn
          class="mx-1 pa-2 pa-sm-4"
          @click="exportSelection()"
        ><v-icon>mdi-export</v-icon>export selection
        </v-btn>
      </template>

    </v-data-table>
  </v-container>
</template>

<script>
import { ExportToCsv } from 'export-to-csv';

export default {
  name: "Home",
  props: {
    active : {
      type: Boolean,
      default: true,
    }
  },
  data: () => ({
    search: "",
    members: [],
    headers: [
      { text: "Name", value: "name" },
      { text: "Groups", value: "groups", sortable: false },
      { text: "", value: "actions", sortable: false, align: "right" },
    ],
    groups: [],
    groupsFilterVisible: false,
    searchVisible: false,
    groupsFilter: [],
  }),
  computed: {
    filteredMembers() {
      let members = this.members
      // filter for active status
      if (this.active) { members = members.filter(m => m.b_active) }
      else { members = members.filter(m => !m.b_active) }

      // filter for the groups input
      if (this.groupsFilter.length > 0) {
        members = members
        .filter((member) =>
          member.groups
            .map((group) => group.i_group)
            .some((g) => this.groupsFilter.includes(g))
        )
      }

      // now filter for the search word
      return members.filter((member) => 
          String(
            member.s_name1 + ' ' + member.s_name2 + ' '
            + member.groups.map(g => g.s_name).join()
          ).toUpperCase()
          .indexOf((this.search || '').toUpperCase()) != -1
      );
    },
    editUrl() { return this.active ? '/members/edit/' : '/members/inactive/edit/' },
    addUrl() { return this.active ? '/members/add' : '/members/inactive/add' }
  },
  methods: {
    getMembers() {
      this.$api.get("/member").then((response) => {
        this.members = response.data.payload;
      });
    },
    getGroups() {
      this.$api.get("/group").then((response) => {
        this.groups = response.data.payload;
      });
    },
    deleteMember(i_member, name) {
      this.$root
        .$confirm(
          "Delete member",
          `Are you sure you want to delete the member ${name}?`,
          { color: "secondary" }
        )
        .then((confirm) => {
          if (confirm) {
            this.$api.delete(`/member/${i_member}`).then(() => {
              this.$root.$emit("reloadData");
            });
          }
        });
    },
    switchMemberActive(i_member, active) {
      this.$api.put(`/member/${i_member}`, { b_active : Number(Boolean(active)) })
      .then(() => {
        this.$root.$emit("reloadData");
      });
    },
    switchGroupFilter(i_group) {
      if (this.groupsFilter.includes(i_group)) {
        this.groupsFilter = this.groupsFilter.filter((el) => el != i_group);
      } else {
        this.groupsFilter.push(i_group);
      }
    },
    exportSelection() {
      // create a CSV with the currently selected members and download it
      let csvMembers = this.filteredMembers.map(m => {
        return {
          "first name"    : m.s_name1 || '',
          "second name"   : m.s_name2 || '',
          "email address" : m.s_email || '',
          "phone number"  : m.s_phone || '',
          "birthdate"     : m.d_birthdate || '',
          "groups"        : m.groups.map(g => g.s_name).join(', ') || '',
          "inserted"      : m.d_inserted || '',
        }
      })
      const csvExporter = new ExportToCsv({
            filename: 'members',
            showLabels: true, 
            useKeysAsHeaders: true,
      })
      csvExporter.generateCsv(csvMembers)
    }
  },
  mounted() {
    this.$root.$on("reloadData", () => {
      this.getMembers();
      this.getGroups();
    });
  },
};
</script>
