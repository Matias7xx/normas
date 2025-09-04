<template>
  <nav class="bg-[#1a1a1a] shadow-lg border-b-4 border-[#c1a85a] sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex items-center justify-center h-16">
        <!-- Brand/Logo -->
        <!-- <Link
          href="/"
          class="flex items-center space-x-3 fade-in hover:opacity-80 transition-opacity duration-300"
        >
          <img
            src="/images/brasao_pcpb.png"
            alt="Logo PCPB"
            class="h-10 w-10 opacity-90 brightness-110 flex-shrink-0"
          />
          <div class="brand-text">
            <h4 class="text-white font-bold text-lg mb-0 leading-tight">
              Biblioteca de Normas
            </h4>
            <small class="text-gray-400 text-sm">
              Polícia Civil da Paraíba
            </small>
          </div>
        </Link> -->

        <div class="hidden md:flex items-center space-x-6">
          <Link
            href="/"
            :class="isActive('/') ? 'text-[#c1a85a]' : 'text-gray-300 hover:text-[#c1a85a]'"
            class="nav-link transition-colors duration-300 flex items-center px-3 py-2"
            title="Página Inicial"
          >
            <i class="fas fa-home mr-2"></i>
            <span>Início</span>
          </Link>

          <Link
            href="/consulta"
            :class="isActive('/consulta') ? 'text-[#c1a85a]' : 'text-gray-300 hover:text-[#c1a85a]'"
            class="nav-link transition-colors duration-300 flex items-center px-3 py-2"
            title="Consultar Normas"
          >
            <i class="fas fa-search mr-2"></i>
            <span>Consultar</span>
          </Link>

          <Link
            href="/especificacoes"
            :class="isActive('/especificacoes') ? 'text-[#c1a85a]' : 'text-gray-300 hover:text-[#c1a85a]'"
            class="nav-link transition-colors duration-300 flex items-center px-3 py-2"
            title="Especificações Técnicas"
          >
            <i class="fas fa-tools mr-2"></i>
            <span>Especificações</span>
          </Link>

          <a
            href="/boletins"
            :class="isActive('/boletins') ? 'text-[#c1a85a]' : 'text-gray-300 hover:text-[#c1a85a]'"
            class="nav-link transition-colors duration-300 flex items-center px-3 py-2"
            title="Boletins Informativos"
          >
            <i class="fas fa-newspaper mr-2"></i>
            <span>Boletim Interno</span>
          </a>

          <button
            @click="$emit('show-help')"
            class="nav-link text-gray-300 hover:text-[#c1a85a] transition-colors duration-300 flex items-center px-3 py-2"
            title="Ajuda"
          >
            <i class="fas fa-question-circle mr-2"></i>
            <span>Ajuda</span>
          </button>

          <!-- Desktop: Mostrar Sair se logado e Área Administrativa sempre -->
          <template v-if="page.props.auth?.user">
            <button
              @click="logout"
              class="nav-link text-gray-300 hover:text-red-500 px-4 py-2 transition-colors duration-300 flex items-center"
              title="Sair"
            >
              <i class="fas fa-sign-out-alt mr-2"></i>
              <span>Sair</span>
            </button>
          </template>
          <a
            href="/login"
            class="bg-[#9c8642] hover:bg-[#8d793f] text-gray-900 px-4 py-2 rounded-lg font-medium transition-all duration-300 hover:scale-105 shadow-md flex items-center md:block hidden"
            title="Área Administrativa"
          >
            <i class="fas fa-cog mr-2"></i>
            <span>Administração</span>
          </a>
        </div>

        <!-- Mobile menu -->
        <div class="md:hidden absolute right-4">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="text-gray-300 hover:text-[#c1a85a] focus:outline-none focus:text-[#c1a85a] transition-colors duration-300"
          >
            <i :class="mobileMenuOpen ? 'fas fa-times' : 'fas fa-bars'" class="text-xl"></i>
          </button>
        </div>
      </div>

      <!-- Navegação Mobile -->
      <div
        v-show="mobileMenuOpen"
        class="md:hidden py-4 border-t border-gray-700 animate-fade-in"
      >
        <div class="flex flex-col space-y-3">
          <Link
            href="/"
            :class="isActive('/') ? 'text-[#c1a85a] bg-gray-800' : 'text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800'"
            class="px-3 py-2 rounded transition-colors duration-300 flex items-center"
            @click="mobileMenuOpen = false"
          >
            <i class="fas fa-home mr-3"></i>
            Início
          </Link>

          <Link
            href="/consulta"
            :class="isActive('/consulta') ? 'text-[#c1a85a] bg-gray-800' : 'text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800'"
            class="px-3 py-2 rounded transition-colors duration-300 flex items-center"
            @click="mobileMenuOpen = false"
          >
            <i class="fas fa-search mr-3"></i>
            Consultar
          </Link>

          <Link
            href="/especificacoes"
            :class="isActive('/especificacoes') ? 'text-[#c1a85a] bg-gray-800' : 'text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800'"
            class="px-3 py-2 rounded transition-colors duration-300 flex items-center"
            @click="mobileMenuOpen = false"
          >
            <i class="fas fa-tools mr-3"></i>
            Especificações
          </Link>

          <a
            href="/boletins"
            :class="isActive('/boletins') ? 'text-[#c1a85a] bg-gray-800' : 'text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800'"
            class="px-3 py-2 rounded transition-colors duration-300 flex items-center"
            @click="mobileMenuOpen = false"
          >
            <i class="fas fa-newspaper mr-3"></i>
            Boletim Interno
          </a>

          <button
            @click="$emit('show-help'); mobileMenuOpen = false"
            class="text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800 px-3 py-2 rounded transition-colors duration-300 flex items-center text-left w-full"
          >
            <i class="fas fa-question-circle mr-3"></i>
            Ajuda
          </button>

          <!-- Mobile: Mostrar só Sair se logado -->
          <template v-if="page.props.auth?.user">
            <button
              @click="logout; mobileMenuOpen = false"
              class="text-gray-300 hover:text-red-500 hover:bg-gray-800 px-3 py-2 rounded transition-colors duration-300 flex items-center text-left w-full"
            >
              <i class="fas fa-sign-out-alt mr-3"></i>
              Sair
            </button>
          </template>
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
