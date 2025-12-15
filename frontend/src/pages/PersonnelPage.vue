<template>
  <div class="q-gutter-md">
    <div class="row items-center">
      <div class="col-12 col-md-6">
        <div class="text-h5">Персонал</div>
        <div class="text-grey-7">CRUD по <b>rail.personnel</b> + связи со станциями/должностями/бригадами</div>
      </div>

      <div class="col-12 col-md-6 row justify-end q-gutter-sm">
        <q-input
          dense
          outlined
          v-model="q"
          label="Поиск (ФИО/ИНН)"
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
          row-key="person_id"
          :loading="loading"
          flat
          bordered
          separator="horizontal"
          :pagination="{ rowsPerPage: 10 }"
        >
          <template #body-cell-station="props">
            <q-td :props="props">{{ props.row.station?.name ?? '' }}</q-td>
          </template>

          <template #body-cell-position="props">
            <q-td :props="props">{{ props.row.position?.name ?? '' }}</q-td>
          </template>

          <template #body-cell-brigade="props">
            <q-td :props="props">{{ props.row.brigade?.name ?? '—' }}</q-td>
          </template>

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
          <div class="text-h6">{{ form.person_id ? 'Редактировать сотрудника' : 'Новый сотрудник' }}</div>
          <q-space />
          <q-btn flat round icon="close" v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-gutter-md">
          <q-input outlined v-model="form.full_name" label="ФИО" />
          <q-input outlined v-model="form.inn" label="ИНН (10/12 цифр, можно пусто)" />

          <div class="row ">
            <div class="col-12 col-md-6">
              <q-select
                outlined
                v-model="form.station_id"
                :options="stationsOptions"
                emit-value
                map-options
                option-label="label"
                option-value="value"
                label="Станция"
              />
            </div>
            <div class="col-12 col-md-6">
              <q-select
                outlined
                v-model="form.position_id"
                :options="positionsOptions"
                emit-value
                map-options
                option-label="label"
                option-value="value"
                label="Должность"
              />
            </div>
          </div>

          <q-select
            outlined
            clearable
            v-model="form.brigade_id"
            :options="brigadesOptions"
            emit-value
            map-options
            option-label="label"
            option-value="value"
            label="Бригада (необязательно)"
          />

          <div class="row ">
            <div class="col-12 col-md-6">
              <q-input outlined v-model="form.hire_date" label="Дата найма" type="date" />
            </div>
            <div class="col-12 col-md-6">
              <q-input outlined v-model="form.fired_at" label="Дата увольнения (если есть)" type="date" />
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <q-card-actions align="right">
          <q-btn flat label="Отмена" v-close-popup />
          <q-btn color="primary" :label="form.person_id ? 'Сохранить' : 'Создать'" :loading="saving" @click="save" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { api } from 'boot/axios'
import { computed, onMounted, ref } from 'vue'
import { Notify, Dialog } from 'quasar'

const q = ref('')
const rows = ref([])
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)

const stations = ref([])
const positions = ref([])
const brigades = ref([])

const form = ref({
  person_id: null,
  full_name: '',
  inn: '',
  station_id: null,
  position_id: null,
  brigade_id: null,
  hire_date: '',
  fired_at: ''
})

const columns = [
  { name: 'person_id', label: 'ID', field: 'person_id', align: 'left', sortable: true },
  { name: 'full_name', label: 'ФИО', field: 'full_name', align: 'left', sortable: true },
  { name: 'inn', label: 'ИНН', field: 'inn', align: 'left' },
  { name: 'station', label: 'Станция', field: 'station', align: 'left' },
  { name: 'position', label: 'Должность', field: 'position', align: 'left' },
  { name: 'brigade', label: 'Бригада', field: 'brigade', align: 'left' },
  { name: 'hire_date', label: 'Найм', field: 'hire_date', align: 'left' },
  { name: 'fired_at', label: 'Увольнение', field: 'fired_at', align: 'left' },
  { name: 'actions', label: 'Действия', field: 'actions', align: 'right' }
]

const stationsOptions = computed(() => stations.value.map(s => ({ label: s.name, value: s.station_id })))
const positionsOptions = computed(() => positions.value.map(p => ({ label: p.name, value: p.position_id })))
const brigadesOptions = computed(() => brigades.value.map(b => ({ label: `${b.name} (участников: ${b.member_count})`, value: b.brigade_id })))

function resetForm() {
  form.value = {
    person_id: null,
    full_name: '',
    inn: '',
    station_id: null,
    position_id: null,
    brigade_id: null,
    hire_date: '',
    fired_at: ''
  }
}

async function loadLookups() {
  const [st, pos, br] = await Promise.all([
    api.get('/lookups/stations'),
    api.get('/lookups/positions'),
    api.get('/lookups/brigades')
  ])
  stations.value = st.data.data
  positions.value = pos.data.data
  brigades.value = br.data.data
}

async function load() {
  loading.value = true
  try {
    const res = await api.get('/personnel', { params: { q: q.value } })
    rows.value = res.data.data
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Не удалось загрузить персонал' })
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
    person_id: row.person_id,
    full_name: row.full_name ?? '',
    inn: row.inn ?? '',
    station_id: row.station_id ?? row.station?.station_id ?? null,
    position_id: row.position_id ?? row.position?.position_id ?? null,
    brigade_id: row.brigade_id ?? row.brigade?.brigade_id ?? null,
    hire_date: row.hire_date ?? '',
    fired_at: row.fired_at ?? ''
  }
  dialog.value = true
}

async function save() {
  saving.value = true
  try {
    const payload = {
      full_name: form.value.full_name,
      inn: form.value.inn === '' ? null : form.value.inn,
      station_id: form.value.station_id,
      position_id: form.value.position_id,
      brigade_id: form.value.brigade_id ?? null,
      hire_date: form.value.hire_date || undefined,
      fired_at: form.value.fired_at || undefined
    }

    if (form.value.person_id) {
      await api.put(`/personnel/${form.value.person_id}`, payload)
      Notify.create({ type: 'positive', message: 'Сотрудник обновлён' })
    } else {
      await api.post('/personnel', payload)
      Notify.create({ type: 'positive', message: 'Сотрудник создан' })
    }

    dialog.value = false
    await load()
    await loadLookups()
  } catch (e) {
    const msg = e?.response?.data?.message || 'Ошибка сохранения (проверь поля и уникальность ИНН)'
    Notify.create({ type: 'negative', message: msg })
  } finally {
    saving.value = false
  }
}

function confirmDelete(row) {
  Dialog.create({
    title: 'Удалить сотрудника?',
    message: `ID ${row.person_id}: ${row.full_name}`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await api.delete(`/personnel/${row.person_id}`)
      Notify.create({ type: 'positive', message: 'Удалено' })
      await load()
      await loadLookups()
    } catch (e) {
      Notify.create({ type: 'negative', message: 'Не удалось удалить' })
    }
  })
}

onMounted(async () => {
  await loadLookups()
  await load()
})
</script>
