<template>
  <DetailsPopup
    @save="$emit('save')"
    :btnState="btnState"
    :popupTitle="popupTitle"
    closeUrl="/mailboxes"
  >
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
    <div class="pa-4">
      <BooleanInput
        ref="boolInp"
        @change="getSql()"
        :entities="groupsAvail"
        :condition="groups"
      ></BooleanInput>
      <v-row>
        <v-col cols="12">
          <v-text-field disabled v-model="sql"></v-text-field>
        </v-col>
      </v-row>
    </div>
  </DetailsPopup>
</template>

<script>
import DetailsPopup from "@/components/DetailsPopup.vue";
import BooleanInput from "@/components/BooleanInput.vue";

export default {
  name: "MailboxPopup",
  props: ["popupTitle", "mailbox", "btnState", "groupsAvail", "groups"],
  data: function () {
    return {
      showPassword: false,
      sql: "",
    };
  },
  components: {
    DetailsPopup,
    BooleanInput,
  },
  methods: {
    getSql() {
      return (this.sql = this.$refs.boolInp.getSqlExpression("i_group"));
    },
  },
};
</script>