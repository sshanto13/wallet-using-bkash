<template>
  <div class="relative z-50">
    <button
      @click.stop="toggleDropdown"
      type="button"
      class="flex items-center gap-2 px-3 py-2 bg-purple-600/80 hover:bg-purple-600 border border-purple-400/50 rounded-lg text-white transition-all shadow-lg hover:shadow-xl cursor-pointer"
      :title="getTitle()"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="2" y1="12" x2="22" y2="12"></line>
        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
      </svg>
      <span class="text-sm font-semibold whitespace-nowrap">{{ currentLanguageName }}</span>
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'rotate-180': showDropdown }" class="flex-shrink-0 transition-transform">
        <polyline points="6 9 12 15 18 9"></polyline>
      </svg>
    </button>

    <!-- Dropdown Menu -->
    <transition name="dropdown">
      <div
        v-if="showDropdown"
        v-click-outside="closeDropdown"
        key="dropdown-menu"
        class="absolute right-0 mt-2 w-48 bg-slate-800 border border-white/20 rounded-lg shadow-2xl z-[100] overflow-hidden"
        @click.stop
      >
        <div class="py-1">
          <button
            v-for="lang in availableLanguages"
            :key="lang.code"
            @click.stop="switchLanguage(lang.code)"
            type="button"
            :class="[
              'w-full text-left px-4 py-3 flex items-center justify-between transition-colors cursor-pointer',
              currentLanguage === lang.code
                ? 'bg-purple-600/30 text-white'
                : 'text-gray-300 hover:bg-white/10'
            ]"
          >
            <span>{{ lang.nativeName }}</span>
            <svg
              v-if="currentLanguage === lang.code"
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="text-purple-400"
            >
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import i18n from '../services/i18n';

export default {
  name: 'LanguageSwitcher',
  data() {
    // Ensure we have fallback values even if i18n fails
    let currentLang = 'en';
    let availableLangs = [
      { code: 'en', name: 'English', nativeName: 'English' },
      { code: 'bn', name: 'Bangla', nativeName: 'বাংলা' }
    ];
    
    try {
      currentLang = i18n.getLanguage();
      availableLangs = i18n.getAvailableLanguages();
    } catch (error) {
      console.warn('i18n not available, using defaults:', error);
    }
    
    return {
      showDropdown: false,
      currentLanguage: currentLang,
      availableLanguages: availableLangs,
    };
  },
  computed: {
    currentLanguageName() {
      try {
        const lang = this.availableLanguages.find(l => l.code === this.currentLanguage);
        return lang ? lang.nativeName : (this.currentLanguage === 'bn' ? 'বাংলা' : 'English');
      } catch (error) {
        return this.currentLanguage === 'bn' ? 'বাংলা' : 'English';
      }
    }
  },
  mounted() {
    // Listen for language changes
    window.addEventListener('languageChanged', this.handleLanguageChange);
    this.currentLanguage = i18n.getLanguage();
  },
  beforeUnmount() {
    window.removeEventListener('languageChanged', this.handleLanguageChange);
  },
  methods: {
    getTitle() {
      try {
        return i18n.t('language.switch_language');
      } catch (error) {
        return 'Switch Language';
      }
    },
    toggleDropdown(event) {
      if (event) {
        event.stopPropagation();
      }
      this.showDropdown = !this.showDropdown;
      console.log('Dropdown toggled:', this.showDropdown);
    },
    closeDropdown() {
      this.showDropdown = false;
    },
    async switchLanguage(langCode) {
      console.log('Switching language to:', langCode);
      
      if (langCode === this.currentLanguage) {
        this.closeDropdown();
        return;
      }
      
      try {
        // Update local state first for immediate feedback
        this.currentLanguage = langCode;
        
        // Then update i18n service
        await i18n.setLanguage(langCode);
        
        this.closeDropdown();
        
        // Emit event for parent components
        this.$emit('language-changed', langCode);
        
        console.log('Language switched successfully to:', langCode);
      } catch (error) {
        console.error('Failed to switch language:', error);
        // Still update local state even if API call fails
        this.currentLanguage = langCode;
        this.closeDropdown();
      }
    },
    handleLanguageChange(event) {
      if (event && event.detail) {
        this.currentLanguage = event.detail.language;
      }
    }
  },
  directives: {
    'click-outside': {
      mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
          // Don't close if clicking on the button or dropdown
          const button = el.previousElementSibling;
          if (button && (button === event.target || button.contains(event.target))) {
            return;
          }
          if (el === event.target || el.contains(event.target)) {
            return;
          }
          binding.value();
        };
        // Use setTimeout to avoid immediate trigger
        setTimeout(() => {
          document.addEventListener('click', el.clickOutsideEvent);
        }, 100);
      },
      unmounted(el) {
        if (el.clickOutsideEvent) {
          document.removeEventListener('click', el.clickOutsideEvent);
        }
      }
    }
  }
};
</script>

<style scoped>
.rotate-180 {
  transform: rotate(180deg);
  transition: transform 0.2s;
}

/* Dropdown animation */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
