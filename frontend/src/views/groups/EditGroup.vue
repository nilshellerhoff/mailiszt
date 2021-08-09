<template>
  <GroupPopup
    @save="saveGroup()"
    :popupTitle="`Edit group ${group.s_name}`"
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
      groupId: this.$route.params.id,
      group: {},
      btnState: "ready",
    };
  },
  components: {
    GroupPopup,
  },
  methods: {
    getGroup() {
      this.$api.get(`/group/${this.groupId}`).then((response) => {
        this.group = response.data;
      });
    },
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
  mounted() {
    this.getGroup();
  }
};

</script>