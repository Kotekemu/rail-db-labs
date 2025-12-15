<template>
  <CrudTable
    title="Бригады"
    subtitle="CRUD по таблице rail.brigades"
    list-url="/brigades"
    create-url="/brigades"
    :update-url="(f) => `/brigades/${f.brigade_id}`"
    :delete-url="(r) => `/brigades/${r.brigade_id}`"
    row-key="brigade_id"
    :columns="columns"
    :default-form="defaultForm"
    :map-row-to-form="mapRowToForm"
    :build-payload="buildPayload"
    search-label="Поиск по названию"
    create-label="Добавить"
    create-title="Новая бригада"
    edit-title="Редактировать бригаду"
  >
    <template #form="{ form }">
      <q-input outlined v-model="form.name" label="Название бригады" />
    </template>
  </CrudTable>
</template>

<script setup>
import CrudTable from 'src/components/CrudTable.vue'

const columns = [
  { name: 'brigade_id', label: 'ID', field: 'brigade_id', align: 'left', sortable: true },
  { name: 'name', label: 'Название', field: 'name', align: 'left', sortable: true },
  { name: 'member_count', label: 'Участников', field: 'member_count', align: 'left', sortable: true }
]

function defaultForm() {
  return { brigade_id: null, name: '' }
}

function mapRowToForm(row) {
  return { brigade_id: row.brigade_id, name: row.name ?? '' }
}

function buildPayload(form) {
  return { name: form.name }
}
</script>
