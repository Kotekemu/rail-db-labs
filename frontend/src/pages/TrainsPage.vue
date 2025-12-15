<template>
  <CrudTable
    title="Поезда"
    subtitle="CRUD по таблице rail.trains + связь с типом и станцией-владельцем"
    list-url="/trains"
    create-url="/trains"
    :update-url="(f) => `/trains/${f.train_id}`"
    :delete-url="(r) => `/trains/${r.train_id}`"
    row-key="train_id"
    :columns="columns"
    :default-form="defaultForm"
    :map-row-to-form="mapRowToForm"
    :build-payload="buildPayload"
    search-label="Поиск (номер/название)"
    create-label="Добавить"
    create-title="Новый поезд"
    edit-title="Редактировать поезд"
  >
    <template #form="{ form }">
      <q-select
        outlined
        v-model="form.owner_station_id"
        :options="stationsOptions"
        emit-value map-options
        option-label="label" option-value="value"
        label="Станция-владелец"
      />
      <q-input outlined v-model="form.train_number" label="Номер поезда" />
      <q-select
        outlined
        v-model="form.train_type_id"
        :options="trainTypesOptions"
        emit-value map-options
        option-label="label" option-value="value"
        label="Тип поезда"
      />
      <q-input outlined v-model="form.name" label="Название (можно пусто)" />
    </template>
  </CrudTable>
</template>

<script setup>
import CrudTable from 'src/components/CrudTable.vue'
import { api } from 'boot/axios'
import { computed, onMounted, ref } from 'vue'
import { Notify } from 'quasar'

const stations = ref([])
const trainTypes = ref([])

const stationsOptions = computed(() => stations.value.map(s => ({ label: s.name, value: s.station_id })))
const trainTypesOptions = computed(() => trainTypes.value.map(t => ({ label: t.name, value: t.train_type_id })))

const columns = [
  { name: 'train_id', label: 'ID', field: 'train_id', align: 'left', sortable: true },
  { name: 'train_number', label: 'Номер', field: 'train_number', align: 'left', sortable: true },
  { name: 'name', label: 'Название', field: 'name', align: 'left' },
  { name: 'type_name', label: 'Тип', field: 'type_name', align: 'left', sortable: true },
  { name: 'owner_station_name', label: 'Владелец', field: 'owner_station_name', align: 'left', sortable: true }
]

function defaultForm() {
  return { train_id: null, owner_station_id: null, train_number: '', train_type_id: null, name: '' }
}

function mapRowToForm(row) {
  return {
    train_id: row.train_id,
    owner_station_id: row.owner_station_id,
    train_number: row.train_number ?? '',
    train_type_id: row.train_type_id,
    name: row.name ?? ''
  }
}

function buildPayload(form) {
  return {
    owner_station_id: form.owner_station_id,
    train_number: form.train_number,
    train_type_id: form.train_type_id,
    name: form.name === '' ? null : form.name
  }
}

async function loadLookups() {
  try {
    const [st, tt] = await Promise.all([
      api.get('/lookups/stations'),
      api.get('/lookups/train-types')
    ])
    stations.value = st.data.data
    trainTypes.value = tt.data.data
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Не удалось загрузить справочники' })
  }
}

onMounted(loadLookups)
</script>
