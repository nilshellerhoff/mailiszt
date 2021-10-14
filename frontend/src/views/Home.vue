<template>
  <div>
    <v-row>
      <v-col cols="12" sm="6">
        <StatCard>
          <template v-slot:title>{{ members.length }} Members</template>
          <template v-slot:icon><v-icon>mdi-account</v-icon></template>
          <template v-slot:actions
            ><v-btn @click="$router.push('/members')">Manage</v-btn></template
          >
        </StatCard>
      </v-col>
      <v-col cols="12" sm="6">
        <StatCard>
          <template v-slot:title>{{ groups.length }} Groups</template>
          <template v-slot:icon><v-icon>mdi-account</v-icon></template>
          <template v-slot:actions
            ><v-btn @click="$router.push('/groups')">Manage</v-btn></template
          >
        </StatCard>
      </v-col>
      <v-col cols="12" sm="6">
        <StatCard>
          <template v-slot:title>{{ lists.length }} Lists</template>
          <template v-slot:icon><v-icon>mdi-email-multiple</v-icon></template>
          <template v-slot:actions
            ><v-btn @click="$router.push('/mailboxes')">Manage</v-btn></template
          >
        </StatCard>
      </v-col>
      <v-col cols="12" sm="6">
        <StatCard>
          <template v-slot:title>{{ mails.length }} Mails</template>
          <template v-slot:icon><v-icon>mdi-email</v-icon></template>
          <template v-slot:actions
            ><v-btn @click="$router.push('/mails')">View</v-btn></template
          >
        </StatCard>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import MailList from "@/components/MailList.vue";
import StatCard from "@/components/StatCard.vue";

export default {
  name: "Home",
  components: {
    MailList,
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
        .get(`/member/`)
        .then((response) => (this.members = response.data));
      this.$api
        .get(`/group/`)
        .then((response) => (this.groups = response.data));
      this.$api
        .get(`/mailbox/`)
        .then((response) => (this.lists = response.data));
      this.$api
        .get(`/mail/`)
        .then((response) => (this.mails = response.data));
    },
  },
  mounted() {
    this.getStats();
    this.$root.$on("reloadData", () => {
      this.getStats();
    });
  },
};
</script>
