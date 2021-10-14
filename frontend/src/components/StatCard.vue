<template>
  <v-card class="mx-auto">
    <v-list-item three-line>
      <v-list-item-content>
        <p class="text-h4 text--primary">
          <slot name="title"></slot>
        </p>
      </v-list-item-content>

      <v-list-item-avatar tile size="40">
        <slot name="icon"></slot>
      </v-list-item-avatar>
    </v-list-item>
    <v-card-actions>
        <slot name="actions"></slot>
    </v-card-actions>
  </v-card>
</template>