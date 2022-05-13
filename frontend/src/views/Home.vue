<template>
  <v-container fluid class="ma-0 pa-1 pa-sm-4">
    <v-row>
      <v-col cols="12" sm="6">
        <StatCard>
          <template v-slot:title>{{ members.length }} Members</template>
          <template v-slot:icon><v-icon>mdi-account</v-icon></template>
          <template v-slot:actions
            ><v-btn to="/members">Manage</v-btn></template
          >
        </StatCard>
      </v-col>
      <v-col cols="12" sm="6">
        <StatCard>
          <template v-slot:title>{{ groups.length }} Groups</template>
          <template v-slot:icon><v-icon>mdi-account</v-icon></template>
          <template v-slot:actions
            ><v-btn to="/groups">Manage</v-btn></template
          >
        </StatCard>
      </v-col>
      <v-col cols="12" sm="6">
        <StatCard>
          <template v-slot:title>{{ lists.length }} Lists</template>
          <template v-slot:icon><v-icon>mdi-email-multiple</v-icon></template>
          <template v-slot:actions
            ><v-btn to="/mailboxes">Manage</v-btn></template
          >
        </StatCard>
      </v-col>
      <v-col cols="12" sm="6">
        <StatCard>
          <template v-slot:title>{{ mails.length }} Mails</template>
          <template v-slot:icon><v-icon>mdi-email</v-icon></template>
          <template v-slot:actions
            ><v-btn to="/mails">View</v-btn></template
          >
        </StatCard>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import StatCard from "@/components/StatCard.vue";

export default {
  name: "Home",
  components: {
    StatCard,
  },
  data: () => ({
    members: [],
    groups: [],
    lists: [],
    mails: [],
  }),
  methods: {
    getStats() {
      this.$api
        .get(`/member/?fields=i_member`)
        .then((response) => (this.members = response.data));
      this.$api
        .get(`/group/?fields=i_group`)
        .then((response) => (this.groups = response.data));
      this.$api
        .get(`/mailbox/?fields=i_mailbox`)
        .then((response) => (this.lists = response.data));
      this.$api
        .get(`/mail/?fields=i_mail`)
        .then((response) => (this.mails = response.data));
    },
  },
  mounted() {
    this.$root.$on("reloadData", () => {
      this.getStats();
    });
  },
};
</script>
