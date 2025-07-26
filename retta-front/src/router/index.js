import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/components/Login.vue'
import Home from '@/components/Home.vue'


const routes = [
  {
    path: '/home',
    name: 'Home',
    component: Home
  },

  {
    path: '/',
    name: 'Login',
    component: Login
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('authToken')
  if (to.meta.requiresAuth && !token) {
    return next({ name: 'Login' })
  }
  next()
})

export default router
