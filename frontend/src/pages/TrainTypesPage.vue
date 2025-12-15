<template>
  <CrudTable
    title="Типы поездов"
    subtitle="CRUD по таблице rail.train_types"
    list-url="/train-types"
    create-url="/train-types"
    :update-url="(f) => `/train-types/${f.train_type_id}`"
    :delete-url="(r) => `/train-types/${r.train_type_id}`"
    row-key="train_type_id"
    :columns="columns"
    :default-form="defaultForm"
    :map-row-to-form="mapRowToForm"
    :build-payload="buildPayload"
    search-label="Поиск по названию"
    create-label="Добавить"
    create-title="Новый тип"
    edit-title="Редактировать тип"
  >
    <template #form="{ form }">
      <q-input outlined v-model="form.name" label="Название типа" />
      <q-input outlined v-model="form.description" type="textarea" autogrow label="Описание (можно пусто)" />
    </template>
  </CrudTable>
</template>

<script setup>
import CrudTable from 'src/components/CrudTable.vue'

const columns = [
  { name: 'train_type_id', label: 'ID', field: 'train_type_id', align: 'left', sortable: true },
  { name: 'name', label: 'Название', field: 'name', align: 'left', sortable: true },
  { name: 'description', label: 'Описание', field: 'description', align: 'left' }
]

function defaultForm() {
  return { train_type_id: null, name: '', description: '' }
}

function mapRowToForm(row) {
  return { train_type_id: row.train_type_id, name: row.name ?? '', description: row.description ?? '' }
}

function buildPayload(form) {
  return { name: form.name, description: form.description === '' ? null : form.description }
}
</script>
