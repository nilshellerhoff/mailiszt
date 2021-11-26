<template>
  <DetailsPopup
    @save="$emit('save')"
    :btnState="btnState"
    :popupTitle="popupTitle"
    closeUrl="/members"
  >
    <div class="pa-4">
      <v-text-field
        prepend-icon="mdi-account-outline"
        label="First Name"
        v-model="member.s_name1"
      ></v-text-field>
      <v-text-field
        prepend-icon="mdi-account"
        label="Second Name"
        v-model="member.s_name2"
      ></v-text-field>
      <v-text-field
        prepend-icon="mdi-at"
        label="Email"
        v-model="member.s_email"
      ></v-text-field>
      <v-text-field
        prepend-icon="mdi-phone"
        label="Phone"
        v-model="member.s_phone"
      ></v-text-field>
      <BirthdayPicker :member="member" />

      <v-autocomplete
        prepend-icon="mdi-account-group"
        label="Groups"
        v-model="groups.member"
        :items="groupsAvailable"
        item-text="s_name"
        item-value="i_group"
        multiple
        chips
        deletable-chips
        :search-input.sync="groupsSearchInput"
        @change="groupsSearchInput = ''"
      ></v-autocomplete>
      <!-- disable active status for now (has now influence) -->
      <!-- <v-checkbox
        v-model="member.b_active"
        :true-value="1"
        :false-value="0"
        label="Active?"
      ></v-checkbox> -->
    </div>
  </DetailsPopup>
</template>

<script>
import DetailsPopup from "@/components/DetailsPopup.vue";
import BirthdayPicker from "@/components/BirthdayPicker.vue";

export default {
  name: "MemberPopup",
  props: ["popupTitle", "member", "groups", "btnState"],
  data: function () {
    return {
      groupsSearchInput: "",
      groupsAvailable: [],
    };
  },
  components: {
    DetailsPopup,
    BirthdayPicker,
  },
  methods: {
    getAvailableGroups() {
      this.$api.get(`/group`).then((response) => {
        this.groupsAvailable = response.data;
      });
    },
  },
  mounted() {
    this.getAvailableGroups();
    this.$root.$on("reloadData", () => {
      this.getAvailableGroups();
    });
  },
};
</script>