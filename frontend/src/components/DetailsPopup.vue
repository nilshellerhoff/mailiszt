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
          <v-btn icon dark @click="$router.back()">
            <v-icon>mdi-arrow-left</v-icon>
          </v-btn>
          <v-toolbar-title>{{popupTitle}}</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn
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
        <v-card width=98% flat>
            <slot></slot>
        </v-card>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
export default {
  name: "DetailsPopup",
  props: [
    'btnState',
    'popupTitle'
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
