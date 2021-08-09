<template>
  <GroupPopup
    @save="saveGroup()"
    :popupTitle="`Add member`"
    :btnState="btnState"
    :group="group"
    :members="members"
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
      members: {
        group: []
      }
    };
  },
  components: {
    GroupPopup,
  },
  methods: {
    async saveGroup() {
      this.btnState = "loading";
      this.$api.put(`/group/add`, this.group).then((response) => {
        this.$api.put(`/group/${response.data.i_group}/members`, this.members.group).then(() => {
          this.btnState = "done";
          setTimeout(() => {
            this.$root.$emit('reloadData');
            this.$router.back();
          }, 500);
        });
      });
    },
  },
};
</script>
