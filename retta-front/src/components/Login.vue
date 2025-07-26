<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <v-app>
    <div class="login-container">
      <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
      </div>

      <div class="content-wrapper">
        <v-card class="login-card" max-width="800" elevation="0">
          <div class="login-header">
            <v-icon class="logo-icon">mdi-account-circle</v-icon>
            <h1 class="text-h4 font-weight-bold mb-2">Bem-vindo</h1>
            <p class="text-body-1 mb-0 opacity-90">Faça login em sua conta</p>
          </div>

          <div class="login-form">
            <v-form @submit.prevent="login" ref="loginForm">
              <v-text-field
                v-model="email"
                label="Email"
                type="email"
                variant="outlined"
                prepend-inner-icon="mdi-email"
                class="input-field mb-4"
                color="black"
                readonly
              />

              <v-text-field
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                label="Senha"
                variant="outlined"
                prepend-inner-icon="mdi-lock"
                :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                @click:append-inner="showPassword = !showPassword"
                class="input-field mb-4"
                color="black"
                readonly
              />

              <v-btn
                type="submit"
                class="custom-btn text-none"
                size="large"
                block
                :loading="loading"
              >
                <v-icon class="mr-2">mdi-login</v-icon>
                Entrar
              </v-btn>
              <v-divider class="my-4" />
            </v-form>
          </div>
        </v-card>
      </div>
    </div>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000" top>
      {{ snackbar.text }}
      <template #actions>
        <v-btn color="white" variant="text" @click="snackbar.show = false">
          Fechar
        </v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script setup>
import { ref } from 'vue'

const email = ref('admin@admin.com')
const password = ref('admin')
const showPassword = ref(false)
const loading = ref(false)
const snackbar = ref({
  show: false,
  text: '',
  color: 'success'
})

const loginForm = ref(null)

const login = async () => {
  loading.value = true

  setTimeout(() => {
    loading.value = false

    if (email.value === 'admin@admin.com' && password.value === 'admin') {
      showSnackbar('Login realizado com sucesso!', 'success')
      setTimeout(() => {
        window.location.href = 'http://localhost:8080/home'
      }, 1000)
    } else {
      showSnackbar('Credenciais inválidas', 'error')
    }
  }, 1000)
}

const showSnackbar = (text, color = 'success') => {
  snackbar.value.text = text
  snackbar.value.color = color
  snackbar.value.show = true
}
</script>

<style scoped>
.login-container {
  background: linear-gradient(135deg, #000000 0%, #333333 100%);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.login-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  max-width: 800px;
  min-width: 400px;
}

.login-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
}

.login-header {
  background: linear-gradient(135deg, #000000, #444444);
  color: white;
  text-align: center;
  padding: 2rem;
  border-radius: 20px 20px 0 0;
}

.login-form {
  padding: 2rem;
}

.logo-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.9;
}

.custom-btn {
  background: linear-gradient(135deg, #000000, #333333) !important;
  color: white !important;
  border-radius: 12px !important;
  font-weight: 600 !important;
  letter-spacing: 0.5px !important;
  transition: all 0.3s ease !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

.custom-btn:hover {
  background: linear-gradient(135deg, #333333, #555555) !important;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

.forgot-link {
  color: #333 !important;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}

.forgot-link:hover {
  color: #000 !important;
}

.input-field .v-field {
  border-radius: 12px;
}

.floating-shapes {
  position: absolute;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 0;
}

.shape {
  position: absolute;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 50%;
  animation: float 6s ease-in-out infinite;
}

.shape:nth-child(1) {
  width: 80px;
  height: 80px;
  top: 20%;
  left: 10%;
  animation-delay: 0s;
}

.shape:nth-child(2) {
  width: 120px;
  height: 120px;
  top: 60%;
  right: 15%;
  animation-delay: 2s;
}

.shape:nth-child(3) {
  width: 60px;
  height: 60px;
  bottom: 20%;
  left: 20%;
  animation-delay: 4s;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(180deg); }
}

.content-wrapper {
  position: relative;
  z-index: 1;
}
</style>