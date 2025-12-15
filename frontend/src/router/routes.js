const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', redirect: '/stations' },

      { path: 'stations', component: () => import('pages/StationsPage.vue') },
      { path: 'personnel', component: () => import('pages/PersonnelPage.vue') },
      { path: 'routes', component: () => import('pages/RoutesPage.vue') },

      { path: 'positions', component: () => import('pages/PositionsPage.vue') },
      { path: 'brigades', component: () => import('pages/BrigadesPage.vue') },
      { path: 'train-types', component: () => import('pages/TrainTypesPage.vue') },
      { path: 'trains', component: () => import('pages/TrainsPage.vue') },

      { path: 'reports', component: () => import('pages/ReportsPage.vue') }
    ]
  },
  { path: '/:catchAll(.*)*', component: () => import('pages/ErrorNotFound.vue') }
]

export default routes
