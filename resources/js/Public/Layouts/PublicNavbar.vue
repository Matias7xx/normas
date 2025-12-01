<template>
  <nav
    class="bg-[#1a1a1a] shadow-lg border-b-4 border-[#c1a85a] sticky top-0 z-50"
  >
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
            :class="
              isActive('/')
                ? 'text-[#c1a85a]'
                : 'text-gray-300 hover:text-[#c1a85a]'
            "
            class="nav-link transition-colors duration-300 flex items-center px-3 py-2.5 text-base font-medium"
            title="Página Inicial"
          >
            <i class="fas fa-home mr-2 text-lg"></i>
            <span>Início</span>
          </Link>

          <Link
            href="/consulta"
            :class="
              isActive('/consulta')
                ? 'text-[#c1a85a]'
                : 'text-gray-300 hover:text-[#c1a85a]'
            "
            class="nav-link transition-colors duration-300 flex items-center px-3 py-2.5 text-base font-medium"
            title="Consultar Normas"
          >
            <i class="fas fa-search mr-2 text-lg"></i>
            <span>Normas</span>
          </Link>

          <Link
            href="/especificacoes"
            :class="
              isActive('/especificacoes')
                ? 'text-[#c1a85a]'
                : 'text-gray-300 hover:text-[#c1a85a]'
            "
            class="nav-link transition-colors duration-300 flex items-center px-3 py-2.5 text-base font-medium"
            title="Especificações Técnicas"
          >
            <i class="fas fa-tools mr-2 text-lg"></i>
            <span>Especificações</span>
          </Link>

          <a
            href="/boletins"
            :class="
              isActive('/boletins')
                ? 'text-[#c1a85a]'
                : 'text-gray-300 hover:text-[#c1a85a]'
            "
            class="nav-link transition-colors duration-300 flex items-center px-3 py-2.5 text-base font-medium"
            title="Boletins Informativos"
          >
            <i class="fas fa-newspaper mr-2 text-lg"></i>
            <span>Boletim</span>
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
            <!-- Dropdown do Usuário -->
            <div class="relative">
              <button
                @click="userDropdownOpen = !userDropdownOpen"
                class="flex items-center space-x-2 text-gray-300 hover:text-[#c1a85a] px-3 py-2.5 rounded-lg transition-colors duration-300"
              >
                <span class="font-medium">{{ firstNameOnly }}</span>
                <i
                  class="fas fa-chevron-down text-sm transition-transform duration-300"
                  :class="{ 'rotate-180': userDropdownOpen }"
                ></i>
              </button>

              <!-- Dropdown Menu -->
              <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <div
                  v-show="userDropdownOpen"
                  class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg py-1 z-50 border border-gray-200"
                >
                  <!-- Informações do usuário -->
                  <div class="px-4 py-3 border-b border-gray-200">
                    <p class="text-sm font-semibold text-gray-900">
                      {{ userName }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                      Matrícula: {{ userMatricula }}
                    </p>
                  </div>

                  <!-- Opção de sair -->
                  <button
                    @click="logout"
                    class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors duration-200 flex items-center"
                  >
                    <i class="fas fa-sign-out-alt mr-2 text-red-500"></i>
                    <span>Sair</span>
                  </button>
                </div>
              </Transition>
            </div>

            <!-- Botão Administração (apenas para roles permitidas) -->
            <a
              v-if="canAccessAdmin"
              href="/login"
              class="hidden xl:flex bg-[#9c8642] hover:bg-[#8d793f] text-gray-900 px-4 py-2.5 rounded-lg font-semibold transition-all duration-300 hover:scale-105 shadow-md items-center text-base"
              title="Área Administrativa"
            >
              <i class="fas fa-cog mr-2 text-lg"></i>
              <span>Administração</span>
            </a>
          </template>

          <!-- Se não estiver logado, mostrar botão de login -->
          <template v-else>
            <a
              href="/login"
              class="bg-[#9c8642] hover:bg-[#8d793f] text-gray-900 px-4 py-2.5 rounded-lg font-semibold transition-all duration-300 hover:scale-105 shadow-md flex items-center text-base"
              title="Fazer Login"
            >
              <i class="fas fa-sign-in-alt mr-2 text-lg"></i>
              <span>Administração</span>
            </a>
          </template>
        </div>

        <!-- Mobile menu -->
        <div class="lg:hidden">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="text-gray-300 hover:text-[#c1a85a] focus:outline-none focus:text-[#c1a85a] transition-colors duration-300 p-2.5"
          >
            <i
              :class="mobileMenuOpen ? 'fas fa-times' : 'fas fa-bars'"
              class="text-lg"
            ></i>
          </button>
        </div>
      </div>

      <!-- Navegação Mobile -->
      <div
        v-show="mobileMenuOpen"
        class="lg:hidden py-4 border-t border-gray-700 animate-fade-in"
      >
        <div class="flex flex-col space-y-2">
          <!-- Info do usuário no mobile -->
          <div
            v-if="page.props.auth?.user"
            class="px-3 py-3 bg-gray-800 rounded mb-2"
          >
            <div class="flex items-center text-gray-300">
              <div>
                <p class="font-medium">{{ userName }}</p>
                <p class="text-xs text-gray-400">
                  Matrícula: {{ userMatricula }}
                </p>
              </div>
            </div>
          </div>

          <Link
            href="/"
            :class="
              isActive('/')
                ? 'text-[#c1a85a] bg-gray-800'
                : 'text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800'
            "
            class="px-3 py-3 rounded transition-colors duration-300 flex items-center text-base font-medium"
            @click="mobileMenuOpen = false"
          >
            <i class="fas fa-home mr-3 w-5 text-lg"></i>
            Início
          </Link>

          <Link
            href="/consulta"
            :class="
              isActive('/consulta')
                ? 'text-[#c1a85a] bg-gray-800'
                : 'text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800'
            "
            class="px-3 py-3 rounded transition-colors duration-300 flex items-center text-base font-medium"
            @click="mobileMenuOpen = false"
          >
            <i class="fas fa-search mr-3 w-5 text-lg"></i>
            Normas
          </Link>

          <Link
            href="/especificacoes"
            :class="
              isActive('/especificacoes')
                ? 'text-[#c1a85a] bg-gray-800'
                : 'text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800'
            "
            class="px-3 py-3 rounded transition-colors duration-300 flex items-center text-base font-medium"
            @click="mobileMenuOpen = false"
          >
            <i class="fas fa-tools mr-3 w-5 text-lg"></i>
            Especificações
          </Link>

          <a
            href="/boletins"
            :class="
              isActive('/boletins')
                ? 'text-[#c1a85a] bg-gray-800'
                : 'text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800'
            "
            class="px-3 py-3 rounded transition-colors duration-300 flex items-center text-base font-medium"
            @click="mobileMenuOpen = false"
          >
            <i class="fas fa-newspaper mr-3 w-5 text-lg"></i>
            Boletim
          </a>

          <button
            @click="handleShowHelp"
            class="text-gray-300 hover:text-[#c1a85a] hover:bg-gray-800 px-3 py-3 rounded transition-colors duration-300 flex items-center text-left w-full text-base font-medium"
          >
            <i class="fas fa-question-circle mr-3 w-5 text-lg"></i>
            Ajuda
          </button>

          <!-- Divisor -->
          <div
            v-if="page.props.auth?.user"
            class="border-t border-gray-700 my-2"
          ></div>

          <template v-if="page.props.auth?.user">
            <button
              @click="handleMobileLogout"
              class="text-gray-300 hover:text-red-500 hover:bg-gray-800 px-3 py-3 rounded transition-colors duration-300 flex items-center text-left w-full text-base font-medium"
            >
              <i class="fas fa-sign-out-alt mr-3 w-5 text-lg"></i>
              Sair
            </button>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const emit = defineEmits(['show-help']);

const mobileMenuOpen = ref(false);
const userDropdownOpen = ref(false);
const page = usePage();

// verificar se o usuário tem permissão de administração
const canAccessAdmin = computed(() => {
  const user = page.props.auth?.user;
  if (!user || !user.role_id) return false;

  // Roles permitidas: 1, 2, 3, 7
  const allowedRoles = [1, 2, 3, 7];
  return allowedRoles.includes(user.role_id);
});

//pegar o nome completo do usuário
const userName = computed(() => {
  const user = page.props.auth?.user;
  return user?.name || 'Usuário';
});

//pegar apenas o primeiro nome
const firstNameOnly = computed(() => {
  const fullName = userName.value;
  return fullName.split(' ')[0];
});

//pegar a matrícula
const userMatricula = computed(() => {
  const user = page.props.auth?.user;
  return user?.matricula || 'Sem matrícula';
});

const isActive = route => {
  const currentUrl = page.url;
  if (route === '/') {
    return currentUrl === '/';
  }
  return currentUrl.startsWith(route);
};

const handleShowHelp = () => {
  mobileMenuOpen.value = false;
  emit('show-help');
};

const handleMobileLogout = () => {
  mobileMenuOpen.value = false;
  logout();
};

const logout = () => {
  userDropdownOpen.value = false;
  router.post('/logout');
};

// Fechar dropdown ao clicar fora
if (typeof document !== 'undefined') {
  document.addEventListener('click', e => {
    const dropdown = document.querySelector('.relative');
    if (dropdown && !dropdown.contains(e.target)) {
      userDropdownOpen.value = false;
    }
  });
}
</script>

<style scoped>
.fade-in {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fadeInDown 0.3s ease-out;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
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
