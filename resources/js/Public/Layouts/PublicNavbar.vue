<template>
  <nav class="bg-[#1a1a1a] shadow-lg border-b-4 border-[#c1a85a] sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex items-center justify-between h-20">
        <!-- Brand/Logo -->
        <Link
          href="/"
          class="flex items-center space-x-2.5 fade-in hover:opacity-80 transition-opacity duration-300 flex-shrink-0"
        >
          <img
            src="/images/brasao_pcpb.png"
            alt="Logo PCPB"
            class="h-10 w-10 opacity-90 brightness-110 flex-shrink-0"
          />
          <div class="brand-text hidden sm:block">
            <h4 class="text-white font-semibold text-lg mb-0 leading-tight">
              Biblioteca de Normas
            </h4>
            <small class="text-gray-400 text-sm">
              Polícia Civil da Paraíba
            </small>
          </div>
          <!-- mobile -->
          <div class="brand-text sm:hidden">
            <h4 class="text-white font-semibold text-base leading-tight">
              Biblioteca de Normas
            </h4>
          </div>
        </Link>

        <!-- Desktop -->
        <div class="hidden lg:flex items-center space-x-5">
          <Link
            href="/"
            :class="isActive('/') ? 'text-[#c1a85a]' : 'text-gray-300 hover:text-[#c1a85a]'"
            class="nav-link transition-colors duration-300 flex items-center px-3 py-2.5 text-base font-medium"
            title="Página Inicial"
          >
            <i class="fas fa-home mr-2 text-lg"></i>
            <span>Início</span>
          </Link>

          <Link
            href="/consulta"
            :class="isActive('/consulta') ? 'text-[#c1a85a]' : 'text-gray-300 hover:text-[#c1a85a]'"
            class="nav-link transition-colors duration-300 flex items-center px-3 py-2.5 text-base font-medium"
            title="Consultar Normas"
          >
            <i class="fas fa-search mr-2 text-lg"></i>
            <span>Consultar Normas</span>
          </Link>

          <Link
            href="/especificacoes"
            :class="isActive('/especificacoes') ? 'text-[#c1a85a]' : 'text-gray-300 hover:text-[#c1a85a]'"
            class="nav-link transition-colors duration-300 flex items-center px-3 py-2.5 text-base font-medium"
            title="Especificações Técnicas"
          >
            <i class="fas fa-tools mr-2 text-lg"></i>
            <span>Especificações</span>
          </Link>

          <a
            href="/boletins"
            :class="isActive('/boletins') ? 'text-[#c1a85a]' : 'text-gray-300 hover:text-[#c1a85a]'"
            class="nav-link transition-colors duration-300 flex items-center px-3 py-2.5 text-base font-medium"
            title="Boletins Informativos"
          >
            <i class="fas fa-newspaper mr-2 text-lg"></i>
            <span>Boletim Interno</span>
          </a>

          <button
            @click="$emit('show-help')"
            class="nav-link text-gray-300 hover:text-[#c1a85a] transition-colors duration-300 flex items-center px-3 py-2.5 text-base font-medium"
            title="Ajuda"
          >
            <i class="fas fa-question-circle mr-2 text-lg"></i>
            <span>Ajuda</span>
          </button>
        </div>

        <!-- Desktop -->
        <div class="hidden lg:flex items-center space-x-3">
          <template v-if="page.props.auth?.user">
            <button
              @click="logout"
              class="nav-link text-gray-300 hover:text-red-500 px-3 py-2.5 transition-colors duration-300 flex items-center text-base font-medium"
              title="Sair"
            >
              <i class="fas fa-sign-out-alt mr-2 text-lg"></i>
              <span>Sair</span>
            </button>
          </template>
          <a
            href="/login"
            class="bg-[#9c8642] hover:bg-[#8d793f] text-gray-900 px-4 py-2.5 rounded-lg font-semibold transition-all duration-300 hover:scale-105 shadow-md flex items-center text-base"
            title="Área Administrativa"
          >
            <i class="fas fa-cog mr-2 text-lg"></i>
            <span class="hidden xl:inline">Administração</span>
            <span class="xl:hidden">Admin</span>
          </a>
        </div>

        <!-- Mobile menu -->
        <div class="lg:hidden">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="text-gray-300 hover:text-[#c1a85a] focus:outline-none focus:text-[#c1a85a] transition-colors duration-300 p-2.5"
          >
            <i :class="mobileMenuOpen ? 'fas fa-times' : 'fas fa-bars'" class="text-lg"></i>
          </button>
        </div>
      </div>

      <!-- Navegação Mobile -->
      <div
        v-show="mobileMenuOpen"
        class="lg:hidden py-4 border-t border-gray-700 animate-fade-in"
      >
        <div class="flex flex-col space-y-2">
          <Link
            href="/"
            :class="isActive('/') ? 'text-[#c1a85a] bg-gray-800' : 'text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800'"
            class="px-3 py-3 rounded transition-colors duration-300 flex items-center text-base font-medium"
            @click="mobileMenuOpen = false"
          >
            <i class="fas fa-home mr-3 w-5 text-lg"></i>
            Início
          </Link>

          <Link
            href="/consulta"
            :class="isActive('/consulta') ? 'text-[#c1a85a] bg-gray-800' : 'text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800'"
            class="px-3 py-3 rounded transition-colors duration-300 flex items-center text-base font-medium"
            @click="mobileMenuOpen = false"
          >
            <i class="fas fa-search mr-3 w-5 text-lg"></i>
            Consultar Normas
          </Link>

          <Link
            href="/especificacoes"
            :class="isActive('/especificacoes') ? 'text-[#c1a85a] bg-gray-800' : 'text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800'"
            class="px-3 py-3 rounded transition-colors duration-300 flex items-center text-base font-medium"
            @click="mobileMenuOpen = false"
          >
            <i class="fas fa-tools mr-3 w-5 text-lg"></i>
            Especificações
          </Link>

          <a
            href="/boletins"
            :class="isActive('/boletins') ? 'text-[#c1a85a] bg-gray-800' : 'text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800'"
            class="px-3 py-3 rounded transition-colors duration-300 flex items-center text-base font-medium"
            @click="mobileMenuOpen = false"
          >
            <i class="fas fa-newspaper mr-3 w-5 text-lg"></i>
            Boletim Interno
          </a>

          <button
            @click="$emit('show-help'); mobileMenuOpen = false"
            class="text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800 px-3 py-3 rounded transition-colors duration-300 flex items-center text-left w-full text-base font-medium"
          >
            <i class="fas fa-question-circle mr-3 w-5 text-lg"></i>
            Ajuda
          </button>

          <template v-if="page.props.auth?.user">
            <button
              @click="logout; mobileMenuOpen = false"
              class="text-gray-300 hover:text-red-500 hover:bg-gray-800 px-3 py-3 rounded transition-colors duration-300 flex items-center text-left w-full text-base font-medium"
            >
              <i class="fas fa-sign-out-alt mr-3 w-5 text-lg"></i>
              Sair
            </button>
          </template>

          <div class="pt-2 border-t border-gray-700 mt-2">
            <a
              href="/login"
              class="bg-[#9c8642] hover:bg-[#8d793f] text-gray-900 px-3 py-3 rounded-lg font-semibold transition-all duration-300 flex items-center justify-center mx-3 text-base"
              @click="mobileMenuOpen = false"
            >
              <i class="fas fa-cog mr-2 text-lg"></i>
              Área Administrativa
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

const emit = defineEmits(['show-help'])

const mobileMenuOpen = ref(false)
const page = usePage()

const isActive = (route) => {
  const currentUrl = page.url
  if (route === '/') {
    return currentUrl === '/'
  }
  return currentUrl.startsWith(route)
}

const logout = () => {
  router.post('/logout')
}
</script>

<style scoped>
.fade-in {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
  animation: fadeInDown 0.3s ease-out;
}

@keyframes fadeInDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.nav-link {
  position: relative;
}

.nav-link::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: -2px;
  left: 50%;
  background-color: #c1a85a;
  transition: all 0.3s ease;
  transform: translateX(-50%);
}

.nav-link:hover::after {
  width: 100%;
}
</style>
