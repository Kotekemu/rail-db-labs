<template>
  <div class="q-gutter-md">
    <div>
      <div class="text-h5">Отчеты</div>
      <div class="text-grey-7">
        2 таблицы, группировка, вычисляемые поля, итоги, фильтры и сортировка перед формированием.
      </div>
    </div>

    <q-tabs v-model="tab" dense class="text-primary" align="left" narrow-indicator>
      <q-tab name="dep" label="Загруженность отправлений" />
      <q-tab name="brig" label="Состав бригад" />
      <q-tab name="tt" label="Маршруты по типам поездов" />
    </q-tabs>
    <q-separator />

    <q-tab-panels v-model="tab" animated>
      <q-tab-panel name="dep" class="q-pa-none">
        <q-card class="q-pa-md">
          <div class="text-subtitle1 q-mb-sm">Параметры отчета</div>
          <div class="row ">
            <div class="col-12 col-md-4">
              <q-select outlined v-model="dep.station_id" :options="stationsOptions" emit-value map-options
                        option-label="label" option-value="value" clearable label="Станция (фильтр)" />
            </div>
            <div class="col-12 col-md-4">
              <q-input outlined v-model="dep.date_from" type="date" label="Дата от (отправление)" />
            </div>
            <div class="col-12 col-md-4">
              <q-input outlined v-model="dep.date_to" type="date" label="Дата до (отправление)" />
            </div>
          </div>

          <div class="row  q-mt-sm">
            <div class="col-12 col-md-4">
              <q-select outlined v-model="dep.sort_by" :options="depSortOptions" emit-value map-options
                        option-label="label" option-value="value" label="Сортировать по" />
            </div>
            <div class="col-12 col-md-4">
              <q-select outlined v-model="dep.sort_dir" :options="dirOptions" emit-value map-options
                        option-label="label" option-value="value" label="Направление" />
            </div>
            <div class="col-12 col-md-4 row items-end">
              <q-btn color="primary" icon="play_arrow" label="Сформировать" :loading="loading" @click="runDepartures" />
            </div>
          </div>
        </q-card>

        <q-card class="q-mt-md">
          <q-card-section>
            <q-table
              title="Результат"
              :rows="depRows"
              :columns="depColumns"
              row-key="station_id"
              flat bordered
              :loading="loading"
              :pagination="{ rowsPerPage: 10 }"
            />
            <q-separator class="q-my-md" />
            <div class="row ">
              <div class="col-12 col-md-4">
                <q-banner dense class="bg-grey-2">Итого маршрутов: <b>{{ depTotals.total_routes ?? 0 }}</b></q-banner>
              </div>
              <div class="col-12 col-md-4">
                <q-banner dense class="bg-grey-2">Итого минут в пути: <b>{{ round(depTotals.total_duration_min) }}</b></q-banner>
              </div>
              <div class="col-12 col-md-4">
                <q-banner dense class="bg-grey-2">Средняя длительность (мин): <b>{{ round(depTotals.avg_duration_min_overall) }}</b></q-banner>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </q-tab-panel>

      <q-tab-panel name="brig" class="q-pa-none">
        <q-card class="q-pa-md">
          <div class="text-subtitle1 q-mb-sm">Параметры отчета</div>
          <div class="row ">
            <div class="col-12 col-md-4">
              <q-select outlined v-model="brig.station_id" :options="stationsOptions" emit-value map-options
                        option-label="label" option-value="value" clearable label="Станция (фильтр)" />
            </div>
            <div class="col-12 col-md-4">
              <q-select outlined v-model="brig.position_id" :options="positionsOptions" emit-value map-options
                        option-label="label" option-value="value" clearable label="Должность (фильтр)" />
            </div>
            <div class="col-12 col-md-4">
              <q-select outlined v-model="brig.only_active" :options="onlyActiveOptions" emit-value map-options
                        option-label="label" option-value="value" label="Только активные" />
            </div>
          </div>

          <div class="row  q-mt-sm">
            <div class="col-12 col-md-4">
              <q-select outlined v-model="brig.sort_by" :options="brigSortOptions" emit-value map-options
                        option-label="label" option-value="value" label="Сортировать по" />
            </div>
            <div class="col-12 col-md-4">
              <q-select outlined v-model="brig.sort_dir" :options="dirOptions" emit-value map-options
                        option-label="label" option-value="value" label="Направление" />
            </div>
            <div class="col-12 col-md-4 row items-end">
              <q-btn color="primary" icon="play_arrow" label="Сформировать" :loading="loading" @click="runBrigades" />
            </div>
          </div>
        </q-card>

        <q-card class="q-mt-md">
          <q-card-section>
            <q-table
              title="Результат"
              :rows="brigRows"
              :columns="brigColumns"
              row-key="brigade_id"
              flat bordered
              :loading="loading"
              :pagination="{ rowsPerPage: 10 }"
            />
            <q-separator class="q-my-md" />
            <div class="row ">
              <div class="col-12 col-md-4">
                <q-banner dense class="bg-grey-2">Итого сотрудников: <b>{{ brigTotals.total_people ?? 0 }}</b></q-banner>
              </div>
              <div class="col-12 col-md-4">
                <q-banner dense class="bg-grey-2">Активных: <b>{{ brigTotals.total_active ?? 0 }}</b></q-banner>
              </div>
              <div class="col-12 col-md-4">
                <q-banner dense class="bg-grey-2">Средний % активных: <b>{{ round(brigTotals.avg_active_percent) }}</b></q-banner>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </q-tab-panel>

      <q-tab-panel name="tt" class="q-pa-none">
        <q-card class="q-pa-md">
          <div class="text-subtitle1 q-mb-sm">Параметры отчета</div>
          <div class="row ">
            <div class="col-12 col-md-4">
              <q-input outlined v-model="tt.date_from" type="date" label="Дата от (отправление)" />
            </div>
            <div class="col-12 col-md-4">
              <q-input outlined v-model="tt.date_to" type="date" label="Дата до (отправление)" />
            </div>
            <div class="col-12 col-md-4"></div>
          </div>

          <div class="row  q-mt-sm">
            <div class="col-12 col-md-4">
              <q-select outlined v-model="tt.sort_by" :options="ttSortOptions" emit-value map-options
                        option-label="label" option-value="value" label="Сортировать по" />
            </div>
            <div class="col-12 col-md-4">
              <q-select outlined v-model="tt.sort_dir" :options="dirOptions" emit-value map-options
                        option-label="label" option-value="value" label="Направление" />
            </div>
            <div class="col-12 col-md-4 row items-end">
              <q-btn color="primary" icon="play_arrow" label="Сформировать" :loading="loading" @click="runTrainTypes" />
            </div>
          </div>
        </q-card>

        <q-card class="q-mt-md">
          <q-card-section>
            <q-table
              title="Результат"
              :rows="ttRows"
              :columns="ttColumns"
              row-key="train_type_id"
              flat bordered
              :loading="loading"
              :pagination="{ rowsPerPage: 10 }"
            />
            <q-separator class="q-my-md" />
            <div class="row ">
              <div class="col-12 col-md-6">
                <q-banner dense class="bg-grey-2">Итого маршрутов: <b>{{ ttTotals.total_routes ?? 0 }}</b></q-banner>
              </div>
              <div class="col-12 col-md-6">
                <q-banner dense class="bg-grey-2">Средняя длительность (мин): <b>{{ round(ttTotals.avg_duration_min_overall) }}</b></q-banner>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </q-tab-panel>
    </q-tab-panels>
  </div>
</template>

<script setup>
import { api } from 'boot/axios'
import { computed, onMounted, ref } from 'vue'
import { Notify } from 'quasar'

const tab = ref('dep')
const loading = ref(false)

const stations = ref([])
const positions = ref([])

const dep = ref({ station_id: null, date_from: '', date_to: '', sort_by: 'routes_count', sort_dir: 'desc' })
const brig = ref({ station_id: null, position_id: null, only_active: '0', sort_by: 'people_count', sort_dir: 'desc' })
const tt = ref({ date_from: '', date_to: '', sort_by: 'routes_count', sort_dir: 'desc' })

const depRows = ref([])
const brigRows = ref([])
const ttRows = ref([])

const depTotals = ref({})
const brigTotals = ref({})
const ttTotals = ref({})

const stationsOptions = computed(() => stations.value.map(s => ({ label: s.name, value: s.station_id })))
const positionsOptions = computed(() => positions.value.map(p => ({ label: p.name, value: p.position_id })))

const dirOptions = [
  { label: 'По возрастанию', value: 'asc' },
  { label: 'По убыванию', value: 'desc' }
]

const onlyActiveOptions = [
  { label: 'Нет', value: '0' },
  { label: 'Да', value: '1' }
]

const depSortOptions = [
  { label: 'Кол-во маршрутов', value: 'routes_count' },
  { label: 'Средняя длительность (мин)', value: 'avg_duration_min' },
  { label: 'Сумма длительности (мин)', value: 'total_duration_min' },
  { label: 'Название станции', value: 'station_name' }
]

const brigSortOptions = [
  { label: 'Название бригады', value: 'brigade_name' },
  { label: 'Кол-во сотрудников', value: 'people_count' },
  { label: 'Активных', value: 'active_count' },
  { label: '% активных', value: 'active_percent' }
]

const ttSortOptions = [
  { label: 'Кол-во маршрутов', value: 'routes_count' },
  { label: 'Средняя длительность (мин)', value: 'avg_duration_min' },
  { label: 'Тип поезда', value: 'train_type' }
]

const depColumns = [
  { name: 'station_name', label: 'Станция', field: 'station_name', align: 'left', sortable: true },
  { name: 'routes_count', label: 'Маршрутов', field: 'routes_count', align: 'left', sortable: true },
  { name: 'avg_duration_min', label: 'Среднее (мин)', field: 'avg_duration_min', align: 'left', format: v => round(v), sortable: true },
  { name: 'total_duration_min', label: 'Сумма (мин)', field: 'total_duration_min', align: 'left', format: v => round(v), sortable: true }
]

const brigColumns = [
  { name: 'brigade_name', label: 'Бригада', field: 'brigade_name', align: 'left', sortable: true },
  { name: 'people_count', label: 'Всего', field: 'people_count', align: 'left', sortable: true },
  { name: 'active_count', label: 'Активных', field: 'active_count', align: 'left', sortable: true },
  { name: 'fired_count', label: 'Уволено', field: 'fired_count', align: 'left', sortable: true },
  { name: 'active_percent', label: '% активных', field: 'active_percent', align: 'left', format: v => round(v), sortable: true }
]

const ttColumns = [
  { name: 'train_type', label: 'Тип поезда', field: 'train_type', align: 'left', sortable: true },
  { name: 'routes_count', label: 'Маршрутов', field: 'routes_count', align: 'left', sortable: true },
  { name: 'avg_duration_min', label: 'Среднее (мин)', field: 'avg_duration_min', align: 'left', format: v => round(v), sortable: true }
]

function round(v) {
  const n = Number(v)
  if (!isFinite(n)) return 0
  return Math.round(n * 100) / 100
}

async function loadLookups() {
  try {
    const [st, pos] = await Promise.all([
      api.get('/lookups/stations'),
      api.get('/lookups/positions')
    ])
    stations.value = st.data.data
    positions.value = pos.data.data
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Не удалось загрузить справочники для фильтров' })
  }
}

async function runDepartures() {
  loading.value = true
  try {
    const res = await api.get('/reports/departures', {
      params: {
        station_id: dep.value.station_id ?? undefined,
        date_from: dep.value.date_from || undefined,
        date_to: dep.value.date_to || undefined,
        sort_by: dep.value.sort_by,
        sort_dir: dep.value.sort_dir
      }
    })
    depRows.value = res.data.data
    depTotals.value = res.data.totals
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Ошибка формирования отчета' })
  } finally {
    loading.value = false
  }
}

async function runBrigades() {
  loading.value = true
  try {
    const res = await api.get('/reports/brigades', {
      params: {
        station_id: brig.value.station_id ?? undefined,
        position_id: brig.value.position_id ?? undefined,
        only_active: brig.value.only_active,
        sort_by: brig.value.sort_by,
        sort_dir: brig.value.sort_dir
      }
    })
    brigRows.value = res.data.data
    brigTotals.value = res.data.totals
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Ошибка формирования отчета' })
  } finally {
    loading.value = false
  }
}

async function runTrainTypes() {
  loading.value = true
  try {
    const res = await api.get('/reports/route-by-train-type', {
      params: {
        date_from: tt.value.date_from || undefined,
        date_to: tt.value.date_to || undefined,
        sort_by: tt.value.sort_by,
        sort_dir: tt.value.sort_dir
      }
    })
    ttRows.value = res.data.data
    ttTotals.value = res.data.totals
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Ошибка формирования отчета' })
  } finally {
    loading.value = false
  }
}

onMounted(loadLookups)
</script>
