<template>
  <v-row justify="center" no-gutters>
    <v-dialog
      v-model="dialog"
      persistent
      max-width="800px"
      :fullscreen="$vuetify.breakpoint.xsOnly"
    >
      <v-card>
        <v-toolbar dark color="secondary">
          <v-btn icon dark @click="$router.push(closeUrl)">
            <v-icon>mdi-close</v-icon>
          </v-btn>
          <v-toolbar-title>{{ popupTitle }}</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn
            v-if="btnState"
            large
            width="100"
            :disabled="btnState == 'disabled'"
            :loading="btnState == 'loading'"
            @click="save()"
            :color="
              btnState == 'failed'
                ? 'error'
                : btnState == 'done'
                ? 'success'
                : 'primary'
            "
          >
            <span v-if="btnState === 'ready'">Save</span>
            <v-icon v-if="btnState === 'done'">mdi-check</v-icon>
            <v-icon v-if="btnState === 'failed'">mdi-close</v-icon>
          </v-btn>
        </v-toolbar>
        <div style="position: relative">
          <div class="loading-overlay" v-if="loading">
            <div class="progress">
              <v-progress-linear
                indeterminate
                height="8"
              />
            </div>
          </div>
          <slot></slot>
        </div>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<style scoped>
  .loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.3);
    z-index: 10;
  }

  .loading-overlay .progress {
    background-color: rgba(1, 127, 205, 0.15);
    border-radius: 10px;
    position: absolute;
    padding: 16px 20px 24px 20px;
    width: 60%; height: 10px;
    top: calc(50% - 40px); left: 20%;
  }
</style>

<script>
export default {
  name: "DetailsPopup",
  props: ["btnState", "popupTitle", "closeUrl", "loading"],
  data: () => ({
    dialog: true,
  }),
  methods: {
    save: function () {
      this.$emit("save");
    },
  },
};
</script>
