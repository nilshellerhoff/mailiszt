<template>
  <DetailsPopup
    @save="$emit('save')"
    :btnState="btnState"
    :popupTitle="popupTitle"
    closeUrl="/groups"
  >
    <div class="pa-4">
      <v-text-field
        prepend-icon="mdi-form-textbox"
        label="Name"
        v-model="group.s_name"
      ></v-text-field>

      <v-autocomplete
        prepend-icon="mdi-account-group"
        label="Members"
        v-model="members.group"
        :items="membersAvailable"
        :item-text="(item) => item.s_name1 + ' ' + item.s_name2"
        item-value="i_member"
        multiple
        chips
        deletable-chips
        :search-input.sync="membersSearchInput"
        @change="membersSearchInput = ''"
      ></v-autocomplete>
    </div>
  </DetailsPopup>
</template>

<script>
import DetailsPopup from "@/components/DetailsPopup.vue";

export default {
  name: "GroupPopup",
  props: ["popupTitle", "group", "btnState", "members"],
  data: function () {
    return {
      membersSearchInput: "",
      membersAvailable: [],
    };
  },
  components: {
    DetailsPopup,
  },
  methods: {
    getAvailableMembers() {
      this.$api.get(`/member`).then((response) => {
        this.membersAvailable = response.data;
      });
    },
  },
  mounted() {
    this.getAvailableMembers();
  },
};
</script>