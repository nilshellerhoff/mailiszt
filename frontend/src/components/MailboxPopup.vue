<template>
  <DetailsPopup
    @save="$emit('save')"
    :btnState="btnState"
    :popupTitle="popupTitle"
    closeUrl="/mailboxes"
  >
    <!-- tabs list -->
    <v-tabs v-model="currentTab">
      <v-tab>server</v-tab>
      <v-tab>groups</v-tab>
    </v-tabs>

    <div class="pa-4">
      <!-- server configuration -->
      <v-tabs-items v-model="currentTab">
        <v-tab-item>
          <v-text-field
            prepend-icon="mdi-form-textbox"
            label="Name"
            v-model="mailbox.s_name"
          ></v-text-field>
          <v-text-field
            prepend-icon="mdi-at"
            label="Email Address"
            v-model="mailbox.s_address"
          ></v-text-field>
          <v-row no-gutters>
            <v-col cols="8" class="pr-2">
              <v-text-field
                prepend-icon="mdi-email-receive"
                label="IMAP server"
                v-model="mailbox.s_imapserver"
              ></v-text-field>
            </v-col>
            <v-col cols="4">
              <v-text-field
                prepend-icon=""
                label="IMAP port"
                v-model="mailbox.n_imapport"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row no-gutters>
            <v-col cols="8" class="pr-2">
              <v-text-field
                prepend-icon="mdi-email-send"
                label="SMTP server"
                v-model="mailbox.s_smtpserver"
              ></v-text-field>
            </v-col>
            <v-col cols="4">
              <v-text-field
                prepend-icon=""
                label="SMTP port"
                v-model="mailbox.n_smtpport"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row no-gutters>
            <v-col cols="6" class="pr-2">
              <v-text-field
                prepend-icon="mdi-account"
                label="Username"
                v-model="mailbox.s_username"
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="mailbox.s_password"
                :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                :type="showPassword ? 'text' : 'password'"
                label="Password"
                @click:append="showPassword = !showPassword"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-tab-item>

        <!-- group/user configuration -->
        <v-tab-item>
          <!-- group selector -->
          <span class="text-subtitle-2">Groups selector</span>

          <!-- method -->
          <v-radio-group v-model="mailbox.s_groupsmethod" row hide-details>
            <v-radio label="simple selector" value="simple"></v-radio>
            <v-radio label="logic selector" value="logic"></v-radio>
          </v-radio-group>

          <!-- simple selector -->
          <div class="px-4" v-show="mailbox.s_groupsmethod == 'simple'">
            <v-autocomplete
              prepend-icon="mdi-account-group"
              label="Groups"
              v-model="groups.groups"
              :items="groupsAvail[0].items"
              item-text="text"
              item-value="value"
              multiple
              chips
              deletable-chips
              :search-input.sync="simpleGroupsSearchInput"
              @change="simpleGroupsSearchInput = ''"
            ></v-autocomplete>
          </div>

          <!-- logic selector -->
          <div class="pa-4" v-show="mailbox.s_groupsmethod == 'logic'">
            <BooleanInput
              ref="boolInp"
              @change="getSql()"
              :entities="groupsAvail"
              :condition="groupsLogic"
            ></BooleanInput>
          </div>

          <v-btn @click="openRecipientsPopup()">Show recipients</v-btn>
          <Popup
            ref="recipientsPopup"
            :title="`Recipients for ${mailbox.s_name}`"
            :width="500"
          >
            <v-chip-group column>
              <div v-for="r in recipients" :key="r.i_member">
                <router-link :to="`/members/edit/${r.i_member}`">
                  <v-chip>
                    {{ r.s_name1 + " " + r.s_name2 }}
                  </v-chip>
                </router-link>
              </div>
            </v-chip-group>
          </Popup>
        </v-tab-item>
      </v-tabs-items>
    </div>
  </DetailsPopup>
</template>

<style scoped>
a {
  text-decoration: none;
}
</style>

<script>
import DetailsPopup from "@/components/DetailsPopup.vue";
import BooleanInput from "@/components/BooleanInput.vue";
import Popup from "@/components/Popup.vue";

export default {
  name: "MailboxPopup",
  props: [
    "popupTitle",
    "mailbox",
    "btnState",
    "groups",
    "groupsAvail",
    "groupsLogic",
  ],
  data: function () {
    return {
      showPassword: false,
      recipients: null,
      currentTab: null,
      simpleGroupsSearchInput: "",
    };
  },
  components: {
    DetailsPopup,
    BooleanInput,
    Popup,
  },
  methods: {
    openRecipientsPopup() {
      this.mailbox.j_groups = JSON.stringify(this.groups.groups);
      this.mailbox.j_groupslogic = JSON.stringify(this.groupsLogic);

      this.$api
        .put(`/mailbox/${this.mailbox.i_mailbox || 0}/recipients`, this.mailbox)
        .then((response) => {
          this.recipients = response.data;
          this.$refs.recipientsPopup.open();
        });
    },
  },
};
</script>