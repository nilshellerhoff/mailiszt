<template>
  <GroupPopup
    @save="saveGroup()"
    :popupTitle="`Add member`"
    :btnState="btnState"
    :group="group"
  >
  </GroupPopup>
</template>

<script>
import GroupPopup from "@/components/GroupPopup.vue";

export default {
  name: "EditGroup",
  data: function () {
    return {
      group: {},
      btnState: "ready",
    };
  },
  components: {
    GroupPopup,
  },
  methods: {
    async saveGroup() {
      this.btnState = "loading";
      this.$api.put(`/group/add`, this.group).then(() => {
        this.btnState = "done";
        setTimeout(() => {
          this.$root.$emit("reloadData");
          this.$router.back();
        }, 500);
      });
    },
  },
};
</script>
