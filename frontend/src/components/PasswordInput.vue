<template>
  <v-text-field
    v-model="password"
    :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
    :type="showPassword ? 'text' : 'password'"
    :label="label"
    @click:append="showPassword = !showPassword"
    :prepend-icon="prependIcon"
    :error="error"
    :messages="[message]"
  ></v-text-field>
</template>

<script>
export default {
  name: "PasswordInput",
  props: ["label", "message", "error", "prependIcon"],
  data: () => ({
    password: '',
    showPassword: false
  }),
};
</script>
