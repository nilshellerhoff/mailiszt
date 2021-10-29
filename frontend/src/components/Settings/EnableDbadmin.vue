<template>
    <v-row>
        <v-col cols="auto">
            DBadmin access is {{ status ? 'enabled' : 'disabled' }}
        </v-col>
        <v-col>
            <ActionButton
                ref="button"
                :label="status ? 'disable' : 'enable'"
                @click="setSetting()"
            ></ActionButton>
        </v-col>
    </v-row>
</template>

<script>
import ActionButton from "@/components/ActionButton.vue"

export default {
    components: {
        ActionButton
    },
    data() {
        return {
            status: null
        }
    },
    methods: {
        getSetting() {
            this.$api.get(`/setting/enable_dbadmin`).then(response => {
                this.status = response.data
            })
        },
        setSetting() {
            this.$refs.button.loading(true)
            this.$api.put(`/setting/enable_dbadmin`, !this.status)
            .then(() => {
                this.$refs.button.loading(false)
                this.$refs.button.success()
            })
            .catch(() => {
                this.$refs.button.loading(false)
                this.$refs.button.error()
            })
            .finally(() => {
                this.getSetting()
            })
        }
    },
    mounted() {
        this.getSetting()
        this.$root.$on('reloadData', () => {
            this.getSetting()
        })
    }
}
</script>