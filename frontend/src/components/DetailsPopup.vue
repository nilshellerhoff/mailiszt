2<template>
  <v-row justify="center" no-gutters>
    <v-dialog
      v-model="dialog"
      persistent
      max-width="800px"
      :fullscreen="$vuetify.breakpoint.xsOnly"
    >
      <v-card>
        <v-toolbar dark color="secondary" class="fixed-bar">
          <v-btn icon dark @click="$router.push(closeUrl)">
            <v-icon>mdi-close</v-icon>
          </v-btn>
          <v-toolbar-title>{{popupTitle}}</v-toolbar-title>
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
        <div>
            <slot></slot>
        </div>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<style scoped>
.fixed-bar {
  top: 0;
  position: sticky;
  position: -webkit-sticky; /* for Safari */
  z-index: 2;
}
</style>

<script>
export default {
  name: "DetailsPopup",
  props: [
    'btnState',
    'popupTitle',
    'closeUrl'
  ],
  data: () => ({
    dialog: true
  }),
  methods: {
    save: function() {
      this.$emit('save');
    }
  },
};
</script>
