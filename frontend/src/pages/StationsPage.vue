<template>
  <div class="q-gutter-md">
    <div class="row items-center ">
      <div class="col-12 col-md-6">
        <div class="text-h5">Станции</div>
        <div class="text-grey-7">CRUD по таблице <b>rail.stations</b></div>
      </div>

      <div class="col-12 col-md-6 row justify-end q-gutter-sm">
        <q-input
          dense
          outlined
          v-model="q"
          label="Поиск (название/адрес/ИНН)"
          class="col-12 col-md-7"
          @keyup.enter="load"
        >
          <template #append>
            <q-icon name="search" class="cursor-pointer" @click="load" />
          </template>
        </q-input>
        <q-btn color="primary" icon="add" label="Добавить" @click="openCreate" />
      </div>
    </div>

    <q-card class="shadow-2">
      <q-card-section>
        <q-table
          :rows="rows"
          :columns="columns"
          row-key="station_id"
          :loading="loading"
          flat
          bordered
          separator="horizontal"
          :pagination="{ rowsPerPage: 10 }"
        >
          <template #body-cell-actions="props">
            <q-td :props="props">
              <q-btn dense flat round icon="edit" @click="openEdit(props.row)" />
              <q-btn dense flat round icon="delete" color="negative" @click="confirmDelete(props.row)" />
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <q-dialog v-model="dialog">
      <q-card style="width: 720px; max-width: 96vw;">
        <q-card-section class="row items-center">
          <div class="text-h6">{{ form.station_id ? 'Редактировать станцию' : 'Новая станция' }}</div>
          <q-space />
          <q-btn flat round icon="close" v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-gutter-md">
          <q-input outlined v-model="form.name" label="Название" />
          <q-input outlined v-model="form.address" label="Адрес" type="textarea" autogrow />
          <q-input outlined v-model="form.inn" label="ИНН (10 или 12 цифр, можно пусто)" />
        </q-card-section>

        <q-separator />

        <q-card-actions align="right">
          <q-btn flat label="Отмена" v-close-popup />
          <q-btn color="primary" :label="form.station_id ? 'Сохранить' : 'Создать'" :loading="saving" @click="save" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { api } from 'boot/axios'
import { onMounted, ref } from 'vue'
import { Notify, Dialog } from 'quasar'

const q = ref('')
const rows = ref([])
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)

const form = ref({
  station_id: null,
  name: '',
  inn: '',
  address: ''
})

const columns = [
  { name: 'station_id', label: 'ID', field: 'station_id', align: 'left', sortable: true },
  { name: 'name', label: 'Название', field: 'name', align: 'left', sortable: true },
  { name: 'inn', label: 'ИНН', field: 'inn', align: 'left' },
  { name: 'address', label: 'Адрес', field: 'address', align: 'left' },
  {
    name: 'created_at',
    label: 'Создано',
    field: 'created_at',
    align: 'left',
    format: (v) => (v ? new Date(v).toLocaleString() : '')
  },
  { name: 'actions', label: 'Действия', field: 'actions', align: 'right' }
]

function resetForm() {
  form.value = { station_id: null, name: '', inn: '', address: '' }
}

async function load() {
  loading.value = true
  try {
    const res = await api.get('/stations', { params: { q: q.value } })
    rows.value = res.data.data
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Не удалось загрузить станции' })
  } finally {
    loading.value = false
  }
}

function openCreate() {
  resetForm()
  dialog.value = true
}

function openEdit(row) {
  form.value = {
    station_id: row.station_id,
    name: row.name ?? '',
    inn: row.inn ?? '',
    address: row.address ?? ''
  }
  dialog.value = true
}

async function save() {
  saving.value = true
  try {
    const payload = {
      name: form.value.name,
      inn: form.value.inn === '' ? null : form.value.inn,
      address: form.value.address
    }

    if (form.value.station_id) {
      await api.put(`/stations/${form.value.station_id}`, payload)
      Notify.create({ type: 'positive', message: 'Станция обновлена' })
    } else {
      await api.post('/stations', payload)
      Notify.create({ type: 'positive', message: 'Станция создана' })
    }

    dialog.value = false
    await load()
  } catch (e) {
    const msg = e?.response?.data?.message || 'Ошибка сохранения (проверь ИНН и уникальность)'
    Notify.create({ type: 'negative', message: msg })
  } finally {
    saving.value = false
  }
}

function confirmDelete(row) {
  Dialog.create({
    title: 'Удалить станцию?',
    message: `ID ${row.station_id}: ${row.name}`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await api.delete(`/stations/${row.station_id}`)
      Notify.create({ type: 'positive', message: 'Удалено' })
      await load()
    } catch (e) {
      Notify.create({ type: 'negative', message: 'Не удалось удалить (есть связи: персонал/маршруты/поезда)' })
    }
  })
}

onMounted(load)
</script>
