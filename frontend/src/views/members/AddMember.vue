<template>
  <MemberPopup
    @save="saveMember()"
    :popupTitle="`Add member`"
    :btnState="btnState"
    :member="member"
    :groups="groups"
    :closeUrl="active ? '/members' : '/members/inactive'"
  >
  </MemberPopup>
</template>

<script>
import MemberPopup from "@/components/MemberPopup.vue";

export default {
  name: "AddMember",
  props: {
    active : {
      type: Boolean,
      default: true,
    }
  },
  data: function () {
    return {
      member: {
        b_active: Number(this.active),
      },
      groups: {
        member: [],
      },
      btnState: "ready",
    };
  },
  components: {
    MemberPopup,
  },
  methods: {
    async saveMember() {
      this.btnState = "loading";
      this.$api.put(`/member/add`, this.member).then((response) => {
        this.$api
          .put(`/member/${response.data.payload[0].i_member}/groups`, this.groups.member)
          .then(() => {
            this.btnState = "done";
            setTimeout(() => {
              this.$root.$emit("reloadData");
              this.$router.back();
            }, 500);
          });
      });
    },
  },
};
</script>
