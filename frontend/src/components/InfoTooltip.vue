<template>
  <v-tooltip bottom>
    <template v-slot:activator="{ on, attrs }">
      <v-icon
        v-bind="attrs"
        v-on="on"
      >mdi-information-outline</v-icon>
    </template>
    <span>
      <slot></slot>
    </span>
  </v-tooltip>
</template>