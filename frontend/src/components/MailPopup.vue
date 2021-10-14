<template>
  <DetailsPopup
    :popupTitle="popupTitle"
  >
    <table>
      <tr>
        <td><b>List</b></td>
        <td>{{mail.s_tomail}}</td>
      </tr>
      <tr>
        <td><b>From:</b></td>
        <td>{{mail.s_frommail}}</td>
      </tr>
      <tr>
        <td><b>Subject:</b></td>
        <td>{{mail.s_subject}}</td>
      </tr>
    </table>
    <v-divider class="ma-4"></v-divider>
    <iframe class="mailbody" :srcdoc="getBodyForDisplay()"></iframe>
    <v-row>
      <v-col cols="4">
        Email sent to:
      </v-col>
      <v-spacer></v-spacer>
      <v-col cols="4">
        <v-text-field dense label="Search" v-model="recipientsSearch"></v-text-field>
      </v-col>
    </v-row>
    <v-chip-group>
      <div v-for="r in recipients.filter(r => String(r.s_email).includes(recipientsSearch))
" :key="r.i_mail2member">
        <router-link :to="`/members/edit/${r.i_member}`">
          <v-chip>
            {{r.s_email || "(no email)" }}
          </v-chip>
        </router-link>
      </div>
    </v-chip-group>
  </DetailsPopup>
</template>

<style scoped>
  a {
    text-decoration: none;
  }
  table td {
    padding: 0px 6px;
  }
  .mailbody {
    width: 100%;
    border-style: none;
  }
</style>

<script>
import DetailsPopup from "@/components/DetailsPopup.vue";

export default {
  name: "MemberPopup",
  props: ["popupTitle", "mail", "recipients"],
  data: function () {
    return {
      recipientsSearch: ""
    };
  },
  components: {
    DetailsPopup,
  },
  methods: {
    getBodyForDisplay() {
      return this.mail.s_bodyhtml || this.mail.s_bodytext
    }
  },
};
</script>