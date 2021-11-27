<template>
  <div ref="btnContainer" style="display: inline-block">
    <v-btn
      :color="compColor"
      :loading="isLoading"
      :width="compWidth ? compWidth : ''"
      :type="type"
      @click="$emit('click')"
    >
      <span v-if="!isSuccess && !isError">{{ label }}</span>
      <v-icon v-if="isError">mdi-close</v-icon>
      <v-icon v-if="isSuccess">mdi-check</v-icon>
    </v-btn>
  </div>
</template>

<script>
export default {
  name: "ActionButton",
  props: {
    color: { type: String, default: "primary" },
    label: { type: String, required: true },
    icon: { type: String, required: false },
    width: { type: Number, required: false },
    timeout: { type: Number, default: 2000 },
    type: { type: String, default: "button" },
  },
  data() {
    return {
      isLoading: false,
      isError: false,
      isSuccess: false,
      fixedWidth: null,
    };
  },
  computed: {
    compColor() {
      if (this.isError) return "error";
      if (this.isSuccess) return "success";
      else return this.color;
    },
    compWidth() {
      if (this.width) return this.width;
      if (this.fixedWidth) return this.fixedWidth;
      else return false;
    },
  },
  methods: {
    loading(b = true) {
      if (b) {
        this.fixedWidth = this.$refs.btnContainer.clientWidth;
        this.isLoading = true;
      } else this.isLoading = false;
    },
    error() {
      this.isError = true;
      if (this.isLoading) this.isLoading = false;
      setTimeout(() => {
        this.isError = false;
      }, this.timeout);
    },
    success() {
      this.isSuccess = true;
      if (this.isLoading) this.isLoading = false;
      setTimeout(() => {
        this.isSuccess = false;
      }, this.timeout);
    },
  },
};
</script>
