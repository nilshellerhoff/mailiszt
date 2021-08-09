<template>
    <MemberPopup
      @save="saveMember()"
      :popupTitle="`Edit ${member.s_name1} ${member.s_name2}`"
      :btnState="btnState"
      :member="member"
      :groups="groups"
    >
    </MemberPopup>
</template>

<script>
import MemberPopup from '@/components/MemberPopup.vue'

export default {
  name: "EditMember",
  data: function () {
    return {
      memberId: this.$route.params.id,
      member: {},
      groups: {
          member: [],
          available: [],
      },
      btnState: "ready",
    }
  },
  components: {
      MemberPopup
  },
  methods: {
    getMember() {
      this.$api.get(`/member/${this.memberId}`).then((response) => {
        this.member = response.data;
      });
      this.$api.get(`/member/${this.memberId}/groups`).then((response) => {
        this.groups.member = response.data
      })
      this.$api.get(`/group`).then((response) => {
        this.groups.available = response.data
      })
    },
    async saveMember() {
      this.btnState = "loading";
      this.$api.put(`/member/${this.memberId}`, this.member).then(() => {
        this.$api.put(`/member/${this.memberId}/groups`, this.groups.member).then(() => {
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
    this.getMember();
  },
};
</script>