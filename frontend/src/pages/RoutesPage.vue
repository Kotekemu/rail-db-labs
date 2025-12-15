<template>
  <div class="q-gutter-md">
    <div class="row items-center ">
      <div class="col-12 col-md-6">
        <div class="text-h5">Маршруты</div>
        <div class="text-grey-7">
          Таблица из <b>rail.v_route_overview</b>
        </div>
      </div>

      <div class="col-12 col-md-6 row justify-end q-gutter-sm">
        <q-btn color="primary" icon="add" label="Создать маршрут" @click="openCreateRoute" />
        <q-btn outline color="primary" icon="refresh" label="Обновить" @click="loadOverview" />
      </div>
    </div>

    <q-card class="shadow-2">
      <q-card-section>
        <q-table
          :rows="overviewRows"
          :columns="overviewColumns"
          row-key="route_id"
          :loading="loading"
          flat
          bordered
          separator="horizontal"
          :pagination="{ rowsPerPage: 10 }"
        >
          <template #body-cell-scheduled_departure="props">
            <q-td :props="props">{{ formatDt(props.row.scheduled_departure) }}</q-td>
          </template>
          <template #body-cell-scheduled_arrival="props">
            <q-td :props="props">{{ formatDt(props.row.scheduled_arrival) }}</q-td>
          </template>
          <template #body-cell-actions="props">
            <q-td :props="props">
              <q-btn dense flat icon="visibility" label="Открыть" @click="openStops(props.row)" />
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <q-dialog v-model="routeDialog">
      <q-card style="width: 840px; max-width: 96vw;">
        <q-card-section class="row items-center">
          <div class="text-h6">Создать маршрут</div>
          <q-space />
          <q-btn flat round icon="close" v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-gutter-md">
          <q-input outlined v-model="routeForm.route_code" label="Код маршрута (например R-003)" />

          <div class="row ">
            <div class="col-12 col-md-6">
              <q-select
                outlined
                v-model="routeForm.owner_station_id"
                :options="stationsOptions"
                emit-value map-options
                option-label="label" option-value="value"
                label="Станция-владелец"
              />
            </div>
            <div class="col-12 col-md-6">
              <q-select
                outlined
                v-model="routeForm.train_id"
                :options="trainsOptions"
                emit-value map-options
                option-label="label" option-value="value"
                label="Поезд"
              />
            </div>
          </div>

          <div class="row ">
            <div class="col-12 col-md-6">
              <q-select
                outlined
                v-model="routeForm.departure_station_id"
                :options="stationsOptions"
                emit-value map-options
                option-label="label" option-value="value"
                label="Станция отправления"
              />
            </div>
            <div class="col-12 col-md-6">
              <q-select
                outlined
                v-model="routeForm.arrival_station_id"
                :options="stationsOptions"
                emit-value map-options
                option-label="label" option-value="value"
                label="Станция прибытия"
              />
            </div>
          </div>

          <q-select
            outlined
            clearable
            v-model="routeForm.brigade_id"
            :options="brigadesOptions"
            emit-value map-options
            option-label="label" option-value="value"
            label="Бригада (необязательно)"
          />
        </q-card-section>

        <q-separator />

        <q-card-actions align="right">
          <q-btn flat label="Отмена" v-close-popup />
          <q-btn color="primary" label="Создать" :loading="savingRoute" @click="createRoute" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="stopsDialog">
      <q-card style="width: 980px; max-width: 98vw;">
        <q-card-section class="row items-center">
          <div class="text-h6">
            Остановки маршрута: <span class="text-primary">{{ currentRoute?.route_code }}</span>
          </div>
          <q-space />
          <q-btn flat round icon="close" v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-gutter-md">
          <div class="row ">
            <div class="col-12 col-md-4">
              <q-input outlined v-model.number="stopForm.stop_no" label="№ остановки" type="number" />
            </div>
            <div class="col-12 col-md-8">
              <q-select
                outlined
                v-model="stopForm.station_id"
                :options="stationsOptions"
                emit-value map-options
                option-label="label" option-value="value"
                label="Станция"
              />
            </div>
          </div>

          <div class="row ">
            <div class="col-12 col-md-6">
              <q-input outlined v-model="stopForm.arrival_time" label="Время прибытия" type="datetime-local" />
            </div>
            <div class="col-12 col-md-6">
              <q-input outlined v-model="stopForm.departure_time" label="Время отправления" type="datetime-local" />
            </div>
          </div>

          <div class="row justify-end">
            <q-btn color="primary" icon="add" label="Добавить остановку" :loading="savingStop" @click="addStop" />
          </div>

          <q-separator />

          <q-table
            :rows="stopsRows"
            :columns="stopsColumns"
            row-key="stop_id"
            flat bordered
            :loading="stopsLoading"
            separator="horizontal"
            :pagination="{ rowsPerPage: 8 }"
          >
            <template #body-cell-station="props">
              <q-td :props="props">{{ props.row.station?.name ?? '' }}</q-td>
            </template>

            <template #body-cell-arrival_time="props">
              <q-td :props="props">{{ formatDt(props.row.arrival_time) }}</q-td>
            </template>

            <template #body-cell-departure_time="props">
              <q-td :props="props">{{ formatDt(props.row.departure_time) }}</q-td>
            </template>

            <template #body-cell-actions="props">
              <q-td :props="props">
                <q-btn dense flat round icon="delete" color="negative" @click="deleteStop(props.row)" />
              </q-td>
            </template>
          </q-table>
        </q-card-section>

        <q-separator />

        <q-card-actions align="right">
          <q-btn outline color="primary" label="Обновить таблицу маршрутов" icon="refresh" @click="loadOverview" />
          <q-btn flat label="Закрыть" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { api } from 'boot/axios'
import { computed, onMounted, ref } from 'vue'
import { Notify } from 'quasar'

const loading = ref(false)
const overviewRows = ref([])

const routeDialog = ref(false)
const savingRoute = ref(false)

const stopsDialog = ref(false)
const currentRoute = ref(null)
const stopsLoading = ref(false)
const stopsRows = ref([])
const savingStop = ref(false)

const stations = ref([])
const trains = ref([])
const brigades = ref([])

const routeForm = ref({
  route_code: '',
  owner_station_id: null,
  train_id: null,
  departure_station_id: null,
  arrival_station_id: null,
  brigade_id: null
})

const stopForm = ref({
  stop_no: 1,
  station_id: null,
  arrival_time: '',
  departure_time: ''
})

const overviewColumns = [
  { name: 'route_id', label: 'ID', field: 'route_id', align: 'left', sortable: true },
  { name: 'route_code', label: 'Код', field: 'route_code', align: 'left', sortable: true },
  { name: 'train_number', label: 'Поезд', field: 'train_number', align: 'left' },
  { name: 'train_type', label: 'Тип', field: 'train_type', align: 'left' },
  { name: 'departure_station', label: 'Откуда', field: 'departure_station', align: 'left' },
  { name: 'arrival_station', label: 'Куда', field: 'arrival_station', align: 'left' },
  { name: 'scheduled_departure', label: 'Отправление', field: 'scheduled_departure', align: 'left' },
  { name: 'scheduled_arrival', label: 'Прибытие', field: 'scheduled_arrival', align: 'left' },
  { name: 'brigade', label: 'Бригада', field: 'brigade', align: 'left', format: v => v ?? '—' },
  { name: 'actions', label: 'Действия', field: 'actions', align: 'right' }
]

const stopsColumns = [
  { name: 'stop_no', label: '№', field: 'stop_no', align: 'left', sortable: true },
  { name: 'station', label: 'Станция', field: 'station', align: 'left' },
  { name: 'arrival_time', label: 'Прибытие', field: 'arrival_time', align: 'left' },
  { name: 'departure_time', label: 'Отправление', field: 'departure_time', align: 'left' },
  { name: 'actions', label: 'Действия', field: 'actions', align: 'right' }
]

const stationsOptions = computed(() => stations.value.map(s => ({ label: s.name, value: s.station_id })))
const trainsOptions = computed(() =>
  trains.value.map(t => ({
    label: `${t.train_number}${t.name ? ' — ' + t.name : ''} (${t.type?.name ?? 'тип неизвестен'})`,
    value: t.train_id
  }))
)
const brigadesOptions = computed(() => brigades.value.map(b => ({ label: `${b.name} (участников: ${b.member_count})`, value: b.brigade_id })))

function formatDt(v) {
  if (!v) return '—'
  return new Date(v).toLocaleString()
}

async function loadLookups() {
  const [st, tr, br] = await Promise.all([
    api.get('/lookups/stations'),
    api.get('/lookups/trains'),
    api.get('/lookups/brigades')
  ])
  stations.value = st.data.data
  trains.value = tr.data.data
  brigades.value = br.data.data
}

async function loadOverview() {
  loading.value = true
  try {
    const res = await api.get('/views/route-overview')
    overviewRows.value = res.data.data
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Не удалось загрузить маршруты' })
  } finally {
    loading.value = false
  }
}

function openCreateRoute() {
  routeForm.value = {
    route_code: '',
    owner_station_id: null,
    train_id: null,
    departure_station_id: null,
    arrival_station_id: null,
    brigade_id: null
  }
  routeDialog.value = true
}

async function createRoute() {
  savingRoute.value = true
  try {
    await api.post('/routes', {
      route_code: routeForm.value.route_code,
      owner_station_id: routeForm.value.owner_station_id,
      train_id: routeForm.value.train_id,
      departure_station_id: routeForm.value.departure_station_id,
      arrival_station_id: routeForm.value.arrival_station_id,
      brigade_id: routeForm.value.brigade_id ?? null
    })
    Notify.create({ type: 'positive', message: 'Маршрут создан' })
    routeDialog.value = false
    await loadOverview()
  } catch (e) {
    const msg = e?.response?.data?.message || 'Ошибка создания (проверь уникальность route_code и заполненность)'
    Notify.create({ type: 'negative', message: msg })
  } finally {
    savingRoute.value = false
  }
}

async function openStops(row) {
  currentRoute.value = row
  stopForm.value = { stop_no: 1, station_id: null, arrival_time: '', departure_time: '' }
  stopsDialog.value = true
  await loadStops()
}

async function loadStops() {
  if (!currentRoute.value) return
  stopsLoading.value = true
  try {
    const res = await api.get(`/routes/${currentRoute.value.route_id}/stops`)
    stopsRows.value = res.data.data
    const maxNo = stopsRows.value.reduce((m, s) => Math.max(m, s.stop_no), 0)
    stopForm.value.stop_no = maxNo + 1
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Не удалось загрузить остановки' })
  } finally {
    stopsLoading.value = false
  }
}

function dtLocalToIso(v) {
  if (!v) return null
  const d = new Date(v)
  return isNaN(d.getTime()) ? null : d.toISOString()
}

async function addStop() {
  if (!currentRoute.value) return
  savingStop.value = true
  try {
    await api.post(`/routes/${currentRoute.value.route_id}/stops`, {
      stop_no: stopForm.value.stop_no,
      station_id: stopForm.value.station_id,
      arrival_time: dtLocalToIso(stopForm.value.arrival_time),
      departure_time: dtLocalToIso(stopForm.value.departure_time)
    })
    Notify.create({ type: 'positive', message: 'Остановка добавлена' })
    await loadStops()
  } catch (e) {
    const msg = e?.response?.data?.message || 'Ошибка (проверь уникальность stop_no и корректность времени)'
    Notify.create({ type: 'negative', message: msg })
  } finally {
    savingStop.value = false
  }
}

async function deleteStop(row) {
  try {
    await api.delete(`/route-stops/${row.stop_id}`)
    Notify.create({ type: 'positive', message: 'Остановка удалена' })
    await loadStops()
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Не удалось удалить остановку' })
  }
}

onMounted(async () => {
  await loadLookups()
  await loadOverview()
})
</script>
