<template>
  <CrudTable
    title="Должности"
    subtitle="CRUD по таблице rail.positions"
    list-url="/positions"
    create-url="/positions"
    :update-url="(f) => `/positions/${f.position_id}`"
    :delete-url="(r) => `/positions/${r.position_id}`"
    row-key="position_id"
    :columns="columns"
    :default-form="defaultForm"
    :map-row-to-form="mapRowToForm"
    :build-payload="buildPayload"
    search-label="Поиск по названию"
    create-label="Добавить"
    create-title="Новая должность"
    edit-title="Редактировать должность"
  >
    <template #form="{ form }">
      <q-input outlined v-model="form.name" label="Название должности" />
    </template>
  </CrudTable>
</template>

<script setup>
import CrudTable from 'src/components/CrudTable.vue'

const columns = [
  { name: 'position_id', label: 'ID', field: 'position_id', align: 'left', sortable: true },
  { name: 'name', label: 'Название', field: 'name', align: 'left', sortable: true }
]

function defaultForm() {
  return { position_id: null, name: '' }
}

function mapRowToForm(row) {
  return { position_id: row.position_id, name: row.name ?? '' }
}

function buildPayload(form) {
  return { name: form.name }
}
</script>
