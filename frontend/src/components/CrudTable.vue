<template>
  <div class="q-gutter-md">
    <div class="row items-center">
      <div class="col-12 col-md-6">
        <div class="text-h5">{{ title }}</div>
        <div class="text-grey-7" v-if="subtitle">{{ subtitle }}</div>
      </div>

      <div class="col-12 col-md-6 row justify-end q-gutter-sm">
        <q-input
          dense
          outlined
          v-model="search"
          :label="searchLabel"
          class="col-12 col-md-7"
          @keyup.enter="load"
        >
          <template #append>
            <q-icon name="search" class="cursor-pointer" @click="load" />
          </template>
        </q-input>
        <q-btn color="primary" icon="add" :label="createLabel" @click="openCreate" />
      </div>
    </div>

    <q-card class="shadow-2">
      <q-card-section>
        <q-table
          :rows="rows"
          :columns="columnsComputed"
          :row-key="rowKey"
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
      <q-card style="width: 760px; max-width: 96vw;">
        <q-card-section class="row items-center">
          <div class="text-h6">{{ form[rowKey] ? editTitle : createTitle }}</div>
          <q-space />
          <q-btn flat round icon="close" v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-gutter-md">
          <slot name="form" :form="form" />
        </q-card-section>

        <q-separator />

        <q-card-actions align="right">
          <q-btn flat label="Отмена" v-close-popup />
          <q-btn color="primary" :label="form[rowKey] ? saveLabel : createSubmitLabel" :loading="saving" @click="save" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { api } from 'boot/axios'
import { computed, onMounted, ref } from 'vue'
import { Dialog, Notify } from 'quasar'

const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  listUrl: { type: String, required: true },
  createUrl: { type: String, required: true },
  updateUrl: { type: Function, required: true },
  deleteUrl: { type: Function, required: true },
  rowKey: { type: String, required: true },
  columns: { type: Array, required: true },
  defaultForm: { type: Function, required: true },
  mapRowToForm: { type: Function, required: true },
  buildPayload: { type: Function, required: true },
  searchLabel: { type: String, default: 'Поиск' },
  createLabel: { type: String, default: 'Добавить' },
  createTitle: { type: String, default: 'Создать' },
  editTitle: { type: String, default: 'Редактировать' },
  createSubmitLabel: { type: String, default: 'Создать' },
  saveLabel: { type: String, default: 'Сохранить' }
})

const rows = ref([])
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const search = ref('')
const form = ref(props.defaultForm())

const columnsComputed = computed(() => [
  ...props.columns,
  { name: 'actions', label: 'Действия', field: 'actions', align: 'right' }
])

function openCreate() {
  form.value = props.defaultForm()
  dialog.value = true
}

function openEdit(row) {
  form.value = props.mapRowToForm(row)
  dialog.value = true
}

async function load() {
  loading.value = true
  try {
    const res = await api.get(props.listUrl, { params: { q: search.value } })
    rows.value = res.data.data
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Не удалось загрузить данные' })
  } finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  try {
    const payload = props.buildPayload(form.value)

    if (form.value[props.rowKey]) {
      await api.put(props.updateUrl(form.value), payload)
      Notify.create({ type: 'positive', message: 'Сохранено' })
    } else {
      await api.post(props.createUrl, payload)
      Notify.create({ type: 'positive', message: 'Создано' })
    }

    dialog.value = false
    await load()
  } catch (e) {
    const msg = e?.response?.data?.message || 'Ошибка сохранения'
    Notify.create({ type: 'negative', message: msg })
  } finally {
    saving.value = false
  }
}

function confirmDelete(row) {
  Dialog.create({
    title: 'Удалить запись?',
    message: `ID ${row[props.rowKey]}`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await api.delete(props.deleteUrl(row))
      Notify.create({ type: 'positive', message: 'Удалено' })
      await load()
    } catch (e) {
      Notify.create({ type: 'negative', message: 'Не удалось удалить (возможно, есть связи)' })
    }
  })
}

onMounted(load)
</script>
