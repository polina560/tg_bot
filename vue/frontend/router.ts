import { Component } from 'vue'
import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'
// import { createRouter, createWebHistory, RouteLocationNormalizedLoaded, RouteRecordRaw } from 'vue-router'
import Home from '@frontend/pages/HomePage.vue'
import { useAlert } from '@frontend/components/alerts'

export interface BreadCrumb {
  text: string
  path?: string
  active?: boolean
}

const mainBreadCrumb: BreadCrumb = { text: 'Главная', path: '/' }

const NotFound: Component = { template: '<h2>Page Not Found</h2>' }

declare module 'vue-router' {
  interface RouteMeta {
    requireAuth?: boolean
    breadCrumbs?: () => Promise<BreadCrumb[]>
    // breadCrumbs?: (route: RouteLocationNormalizedLoaded) => Promise<BreadCrumb[]>
    minimumRole?: number
    requiredRole?: number
  }
}

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: Home,
    meta: {
      breadCrumbs: async () => [mainBreadCrumb]
    }
  },
  {
    name: 'NotFound',
    path: '/:pathMatch(.*)*',
    component: NotFound
  }
]
export const router = createRouter({
  history: createWebHistory(),
  routes
})


