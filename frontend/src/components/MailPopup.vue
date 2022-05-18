<template>
  <DetailsPopup :popupTitle="popupTitle" closeUrl="/mails">
    <div class="pa-4">
      <table>
        <tr>
          <td><b>List</b></td>
          <td>{{ mail.s_tomail }}</td>
        </tr>
        <tr>
          <td><b>From:</b></td>
          <td>{{ mail.s_frommail }}</td>
        </tr>
        <tr>
          <td><b>Subject:</b></td>
          <td>{{ mail.s_subject }}</td>
        </tr>
      </table>
      <v-divider class="ma-4"></v-divider>

      <!-- Attachments -->
      <template v-if="mail.n_attachments > 0">
        <v-row>
          <span v-for="attachment in mail.attachments" :key="attachment.i_attachment" class="ma-2">
              <v-icon>mdi-paperclip</v-icon>
              <a :href="apiUrl + '/attachment/' + attachment.s_filename" class="ma-2 body-2" download>
                {{ attachment.s_name }} ({{ getFileSize(attachment.n_size) }})
              </a>
          </span>
        </v-row>
        <v-divider class="ma-4"></v-divider>
      </template>


      <!-- Mail Body -->
      <iframe :height="mailIframeHeight" @load="mailIframeHeight = getMailIframeHeight()" ref="mailIframe" class="mailbody" :srcdoc="getBodyForDisplay()"></iframe>

      <v-divider class="ma-4"></v-divider>
      <!-- Recipients -->
      <v-row>
        <v-col cols="4">
          {{ recipients.length }} recipients{{
            recipients.length > 0 ? ":" : ""
          }}
        </v-col>
        <v-spacer></v-spacer>
        <v-col cols="4">
          <v-text-field
            dense
            label="Search"
            v-model="recipientsSearch"
            v-if="recipients.length > 0"
          ></v-text-field>
        </v-col>
      </v-row>
      <v-chip-group>
        <div
          v-for="r in recipients.filter((r) =>
            String(r.s_email).includes(recipientsSearch)
          )"
          :key="r.i_mail2member"
        >
          <router-link :to="`/members/edit/${r.i_member}`">
            <v-chip>
              {{ r.s_email || "(no email)" }}
            </v-chip>
          </router-link>
        </div>
      </v-chip-group>
    </div>
  </DetailsPopup>
</template>

<style scoped>
a {
  text-decoration: none;
}
table td {
  padding: 0px 6px;
}
/* div.mailbody {
    height: 300px;
    display: block;
    overflow: scroll;
  } */
iframe.mailbody {
  width: 100%;
  /* height: 100%; */
  /* min-height: 300px; */
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
      recipientsSearch: "",
      mailIframeHeight: 0,
    };
  },
  components: {
    DetailsPopup,
  },
  computed: {
    apiUrl: () => process.env.VUE_APP_BASE_URL,
  },
  methods: {
    getFileSize(bytes) {
      if (bytes < 1024) return bytes + " Bytes";
      if (bytes < 1024*1024) return (bytes / 1024).toFixed(1) + " KB";
      if (bytes < 1024*1024*1024) return (bytes / 1024 / 1024).toFixed(1) + " MB";
      return (bytes / 1024 / 1024 / 1024).toFixed(1) + " GB";
    },  
    getBodyForDisplay() {
      return this.mail.s_bodyhtml || this.mail.s_bodytext;
    },
    getMailIframeHeight: function () {
      console.log(this.$refs.mailIframe.contentDocument.body.scrollHeight);
      return this.$refs.mailIframe.contentWindow.document.body.scrollHeight + 20 + "px";
    },

  },
};
</script>