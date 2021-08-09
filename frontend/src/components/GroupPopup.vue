<template>
  <DetailsPopup
    @save="$emit('save')"
    :btnState="btnState"
    :popupTitle="popupTitle">
    <v-text-field prepend-icon="mdi-form-textbox" label="Name" v-model="group.s_name"></v-text-field>
  </DetailsPopup>
</template>

<script>
import DetailsPopup from "@/components/DetailsPopup.vue";

export default {
  name: "GroupPopup",
  props: [
      'popupTitle',
      'group',
      'btnState'
  ],
  data: function() {
    return {
    };
  },
  components: {
    DetailsPopup,
  },
};
</script>