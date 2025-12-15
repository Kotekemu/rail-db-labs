<template>
  <q-layout view="hHh Lpr fFf" class="bg-grey-1">
    <q-header elevated class="bg-primary text-white">
      <q-toolbar>
        <q-btn flat dense round icon="menu" @click="leftDrawerOpen = !leftDrawerOpen" />
        <q-toolbar-title>Railway Station DB</q-toolbar-title>
        <q-chip square color="white" text-color="primary" class="q-ml-sm">
          Quasar + Laravel + PostgreSQL
        </q-chip>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" show-if-above bordered>
      <q-list padding>
        <q-item-label header class="text-grey-8">Разделы</q-item-label>

        <q-item clickable v-ripple to="/stations">
          <q-item-section avatar><q-icon name="location_on" /></q-item-section>
          <q-item-section>Станции</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/personnel">
          <q-item-section avatar><q-icon name="badge" /></q-item-section>
          <q-item-section>Персонал</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/routes">
          <q-item-section avatar><q-icon name="train" /></q-item-section>
          <q-item-section>Маршруты</q-item-section>
        </q-item>

        <q-item-label header class="text-grey-8">Справочники</q-item-label>

        <q-item clickable v-ripple to="/positions">
          <q-item-section avatar><q-icon name="work" /></q-item-section>
          <q-item-section>Должности</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/brigades">
          <q-item-section avatar><q-icon name="groups" /></q-item-section>
          <q-item-section>Бригады</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/train-types">
          <q-item-section avatar><q-icon name="category" /></q-item-section>
          <q-item-section>Типы поездов</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/trains">
          <q-item-section avatar><q-icon name="directions_railway" /></q-item-section>
          <q-item-section>Поезда</q-item-section>
        </q-item>

        <q-separator class="q-my-sm" />

        <q-item-label header class="text-grey-8">Операции</q-item-label>

        <q-item clickable v-ripple to="/stations">
          <q-item-section avatar><q-icon name="location_on" /></q-item-section>
          <q-item-section>Станции</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/personnel">
          <q-item-section avatar><q-icon name="badge" /></q-item-section>
          <q-item-section>Персонал</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/routes">
          <q-item-section avatar><q-icon name="train" /></q-item-section>
          <q-item-section>Маршруты</q-item-section>
        </q-item>

        <q-separator class="q-my-sm" />

        <q-item-label header class="text-grey-8">Отчеты</q-item-label>

        <q-item clickable v-ripple to="/reports">
          <q-item-section avatar><q-icon name="assessment" /></q-item-section>
          <q-item-section>Отчеты</q-item-section>
        </q-item>

      </q-list>
    </q-drawer>

    <q-page-container>
      <q-page class="q-pa-md">
        <router-view />
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'
const leftDrawerOpen = ref(true)
</script>
