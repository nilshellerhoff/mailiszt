
<template>
  <v-dialog v-model="dialog" :max-width="width" :style="{ zIndex: zIndex }" @keydown.esc="cancel">
    <v-card>
      <v-toolbar dark color="secondary" dense flat>
        <v-btn icon dark @click="dialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        <v-toolbar-title class="white--text">{{ title }}</v-toolbar-title>
      </v-toolbar>
      <v-card-text class="pa-4">
        <slot></slot>
      </v-card-text>
      <v-card-actions>
        <slot name="actions"></slot>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: {
    title: String,
    width: {type: Number, default: 300},
    zIndex: {type: Number, default: 200},
  },
  data: () => ({
    dialog: false,
  }),
  methods: {
    open() {
      this.dialog = true
    },
    close() {
      this.dialog = false
    },
  }
}
</script>