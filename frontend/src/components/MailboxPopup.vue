<template>
  <DetailsPopup
    @save="$emit('save')"
    :btnState="btnState"
    :popupTitle="popupTitle"
    closeUrl="/mailboxes"
  >
    <!-- tabs list -->
    <v-tabs v-model="currentTab">
      <v-tab>general</v-tab>
      <v-tab>templates</v-tab>
      <v-tab>server</v-tab>
      <v-tab>groups</v-tab>
    </v-tabs>

    <div class="pa-4">
      <v-tabs-items v-model="currentTab">
        <!-- general configuration -->
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

          <!-- allowed senders -->
          <span class="text-subtitle-1 font-weight-medium">Allowed senders</span>
          <v-row no-gutters align="center">
            <v-col cols=12 sm=5>
              <v-select
                v-model="mailbox.s_allowedsenders"
                :items="allowedSendersItems"
                label="who can address this list"
              >
              </v-select>
            </v-col>
            <v-col cols=0 sm=1></v-col>
            <v-col cols=12 sm=6>
              <v-autocomplete
                :disabled="mailbox.s_allowedsenders != 'specific'"
                v-model="allowedSendersPeople.people"
                label="allowed senders"
                :items="members"
                :item-text="el => el.s_name1 + ' ' + el.s_name2"
                item-value="i_member"
                multiple
                small-chips
                deletable-chips
                :search-input.sync="allowedSendersSearchInput"
                @change="allowedSendersSearchInput = ''"
              ></v-autocomplete>
            </v-col>
          </v-row>

          <!-- reply to -->
          <span class="text-subtitle-1 font-weight-medium">Reply to</span>
          <v-row no-gutters>
            <v-col cols=12 sm=5>
              <v-select
                v-model="mailbox.s_replyto"
                :items="replyToItems"
                label="Default reply-to header"
              ></v-select>
            </v-col>
            <v-col cols=0 sm=1></v-col>
            <v-col cols=12 sm=6>
              <!-- <v-checkbox
                v-model="mailbox.b_overridereplyto"
                label="allow reply to header to be overridden?"
              ></v-checkbox> -->
              <v-select
                v-model="mailbox.s_replytooverride"
                :items="replyToOverrideItems"
                label="If a custom reply-to header is set"
              ></v-select>
            </v-col>
          </v-row>

          <!-- moderator -->
          <v-row no-gutters>
            <v-select
              v-model="mailbox.i_moderator"
              label="Moderator"
              :items="members"
              :item-text="el => el.s_name1 + ' ' + el.s_name2"
              item-value="i_member"
            ></v-select>
          </v-row>

        </v-tab-item>


        <!-- message templates -->
        <v-tab-item>
          <v-row no-gutters>
            <v-text-field
              prepend-icon="mdi-label"
              label="Subject header"
              v-model="mailbox.s_templatesubject"
            ></v-text-field>
          </v-row>
          <v-row no-gutters>
            <v-text-field
              prepend-icon="mdi-account"
              label="From name header"
              v-model="mailbox.s_templatefrom"
            ></v-text-field>
          </v-row>
          <v-row no-gutters>
            <v-textarea
              prepend-icon="mdi-text-long"
              label="Email body"
              v-model="mailbox.s_templatebody"
            ></v-textarea>
          </v-row>
          <v-row no-gutters>
            <v-checkbox
              prepend-icon="mdi-cancel"
              label="Send rejection notices to people not allowed to address this mailbox?"
              v-model="mailbox.b_sendrejectionnotices"
            ></v-checkbox>
          </v-row>
          <v-row no-gutters>
            <v-textarea
              :disabled="!mailbox.b_sendrejectionnotices"
              prepend-icon="mdi-text-remove"
              label="Rejection notice template"
              v-model="mailbox.s_templaterejectionnotice"
            ></v-textarea>
          </v-row>


          The following variables are available:<br>
          <code v-pre>{{subject}}</code> subject of the message<br>
          <code v-pre>{{body}}</code> body of the message<br>
          <code v-pre>{{frommail}}</code> email address of the original sender<br>
          <code v-pre>{{fromname}}</code> name of the original sender (if given)<br>
          <code v-pre>{{listaddress}}</code> email address of the mailinglist<br>
          <code v-pre>{{listname}}</code> name of the mailinglist<br>
          <code v-pre>{{moderatorname}}</code> name of the moderator<br>
          <code v-pre>{{moderatoraddress}}</code> email address of the moderator<br>
          
        </v-tab-item>

        <!-- server configuration -->
        <v-tab-item>
          <v-row no-gutters>
            <v-col cols="6" class="pr-2">
              <v-text-field
                prepend-icon="mdi-email-receive"
                label="IMAP server"
                v-model="mailbox.s_imapserver"
              ></v-text-field>
            </v-col>
            <v-col cols="3" class="pr-2">
              <v-select
                v-model="mailbox.s_imapencryption"
                :items="imapEncryptionItems"
                label="Encryption"
              ></v-select>
            </v-col>
            <v-col cols="3">
              <v-text-field
                prepend-icon=""
                label="IMAP port"
                v-model="mailbox.n_imapport"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row no-gutters align="center">
            <v-col cols=6 class="pr-2">
              <v-text-field
                prepend-icon="mdi-email-send"
                label="SMTP server"
                v-model="mailbox.s_smtpserver"
              ></v-text-field>
            </v-col>
            <v-col cols="3" class="pr-2">
              <v-select
                v-model="mailbox.s_smtpencryption"
                :items="smtpEncryptionItems"
                label="Encryption"
              ></v-select>
            </v-col>
            <v-col cols="3">
              <v-text-field
                label="SMTP port"
                v-model="mailbox.n_smtpport"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row no-gutters>
            <v-col cols="12" sm="6" class="pr-2">
              <v-text-field
                prepend-icon="mdi-account"
                label="Username"
                v-model="mailbox.s_username"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                prepend-icon="mdi-lock"
                v-model="mailbox.s_password"
                :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                :type="showPassword ? 'text' : 'password'"
                label="Password"
                @click:append="showPassword = !showPassword"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row no-gutters>
            <v-checkbox
              prepend-icon=""
              label="Only consider messages sent to the address of the mailbox? This is useful if setting up aliases or a catch-all address which point to a single mailbox but belong to different lists."
              v-model="mailbox.b_consideronlytolistaddress"
            ></v-checkbox>
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
          <div class="pa-4" v-show="mailbox.s_groupsmethod == 'logic'" style="overflow-x: scroll">
            <div style="display: block; min-width: 600px">
            <BooleanInput
              ref="boolInp"
              :entities="groupsAvail"
              :condition="groupsLogic"
            ></BooleanInput>
          </div>
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

          <v-checkbox
            label="include inactive members?"
            v-model="mailbox.b_includeinactive"
          ></v-checkbox>
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
    "allowedSendersPeople",
  ],
  data: function () {
    return {
      showPassword: false,
      recipients: null,
      currentTab: null,
      simpleGroupsSearchInput: "",
      replyToItems: [
        { text : 'Sender', value : 'sender' },
        { text : 'Mailinglist', value : 'mailinglist' },
        { text : 'Sender + Mailinglist', value : 'sender+mailinglist' },
      ],
      replyToOverrideItems: [
        { text : 'use default', value : 'default' },
        { text : 'add reply-to address to list of recipients', value : 'add' },
        { text : 'replace sender in list with the reply-to address', value : 'replacesender' },
        { text : 'only respond to reply-to address', value : 'replace' },
      ],
      allowedSendersItems: [
        { text : 'Everybody', value : 'everybody' },
        { text : 'Registered members', value : 'registered' },
        { text : 'Members of the list', value : 'members' },
        { text : 'Specific people', value : 'specific' },
      ],
      imapEncryptionItems: [
        { text : 'None', value : 'none' },
        { text : 'SSL', value : 'ssl' },
        { text : 'TLS', value : 'tls' },
      ],
      smtpEncryptionItems: [
        { text : 'None', value : 'none' },
        { text : 'SSL', value : 'ssl' },
        { text : 'TLS', value : 'tls' },
      ],
      allowedSendersSearchInput: '',
      members: [],
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
          this.recipients = response.data.payload;
          this.$refs.recipientsPopup.open();
        });
    },

    getMembers() {
      this.$api.get(`/member`).then(response => {
        this.members = response.data.payload
      })
    }
  },
  mounted() {
    this.getMembers()
    this.$root.$on('reloadData', () => {
      this.getMembers()
    })
  }
};
</script>