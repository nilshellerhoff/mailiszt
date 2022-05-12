<template>
  <GroupPopup
    @save="saveGroup()"
    :popupTitle="`Edit group ${group.s_name}`"
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
      groupId: this.$route.params.id,
      group: {},
      members: {
        group: [],
      },
      btnState: "ready",
    };
  },
  components: {
    GroupPopup,
  },
  methods: {
    getGroup() {
      this.$api.get(`/group/${this.groupId}`).then((response) => {
        this.group = response.data
      })
      this.$api.get(`/group/${this.groupId}/members`).then((response) => {
        this.members.group = response.data.map(m => m.i_member)
      })
    },
    async saveGroup() {
      this.btnState = "loading";
      this.$api.put(`/group/${this.groupId}`, this.group).then(() => {
        this.$api.put(`/group/${this.groupId}/members`, this.members.group).then(() => {
          this.btnState = "done";
          setTimeout(() => {
            this.$root.$emit('reloadData');
            this.$router.back();
          }, 500);
        });
      });
    },
  },
  mounted() {
    this.$root.$on('reloadData', () => {
      this.getGroup()
    })
  }
};

</script>