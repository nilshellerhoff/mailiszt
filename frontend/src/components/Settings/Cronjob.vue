<template>
  <span>
    <v-row align="center">
      <v-col cols="auto">
        Email fetching is {{ status ? "enabled" : "disabled" }}
      </v-col>
      <v-col>
        <ActionButton
          ref="button"
          :label="status ? 'disable' : 'enable'"
          @click="setSetting()"
        ></ActionButton>
      </v-col>
    </v-row>
    <span class="text-subtitle-1"
      >Call the following URL periodically in order check for new mails</span
    >
    <v-row>
      <v-col cols="5"
        ><v-text-field
          disabled
          dense
          single-line
          v-model="cronUrl"
        ></v-text-field
      ></v-col>
      <v-col cols="3">
        <v-btn @click="urlCopy">
          <v-icon left>mdi-content-copy</v-icon>
          copy
        </v-btn>
      </v-col>
    </v-row>
  </span>
</template>

<script>
import copy from "copy-to-clipboard";
import ActionButton from "@/components/ActionButton.vue";

export default {
  name: "Cronjob",
  components: {
    ActionButton,
  },
  data: () => ({
    cronUrl: process.env.VUE_APP_BASE_URL + "/mailbox/forward",
    status: null,
  }),
  methods: {
    urlCopy() {
      copy(this.cronUrl);
    },
    getSetting() {
      this.$api.get(`/setting/enable_email_fetching`).then((response) => {
        this.status = response.data.payload;
      });
    },
    setSetting() {
      this.$refs.button.loading(true);
      this.$api
        .put(`/setting/enable_email_fetching`, !this.status)
        .then(() => {
          this.$refs.button.loading(false);
          this.$refs.button.success();
        })
        .catch(() => {
          this.$refs.button.loading(false);
          this.$refs.button.error();
        })
        .finally(() => {
          this.getSetting();
        });
    },
  },
  mounted() {
    this.getSetting();
    this.$root.$on("reloadData", () => {
      this.getSetting();
    });
  },
};
</script>