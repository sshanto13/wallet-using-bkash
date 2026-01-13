/**
 * Internationalization (i18n) Service
 * Handles language switching and translation
 */

class I18nService {
  constructor() {
    // Always default to English - ensure it's 'en' if invalid
    const storedLang = this.getStoredLanguage();
    this.currentLanguage = (storedLang === 'en' || storedLang === 'bn') ? storedLang : 'en';
    this.translations = {};
    
    // Always load English translations first to ensure fallback works
    this.loadTranslations('en').catch(err => {
      console.warn('Failed to load English translations:', err);
    });
    
    // Then load current language if different from English
    if (this.currentLanguage !== 'en') {
      this.loadTranslations(this.currentLanguage).catch(err => {
        console.warn('Failed to load current language translations, falling back to English:', err);
        // If current language fails, switch back to English
        this.currentLanguage = 'en';
        this.setStoredLanguage('en');
      });
    }
  }

  /**
   * Get stored language preference from localStorage
   * Always defaults to 'en' (English)
   */
  getStoredLanguage() {
    if (typeof window !== 'undefined') {
      const stored = localStorage.getItem('app_language');
      // Only use stored value if it's valid, otherwise default to English
      return (stored === 'en' || stored === 'bn') ? stored : 'en';
    }
    return 'en';
  }

  /**
   * Store language preference
   */
  setStoredLanguage(lang) {
    if (typeof window !== 'undefined') {
      localStorage.setItem('app_language', lang);
    }
  }

  /**
   * Load translations from Laravel backend
   */
  async loadTranslations(lang = null) {
    const language = lang || this.currentLanguage;
    
    try {
      const response = await fetch(`/api/v1/language/${language}`);
      if (response.ok) {
        const translations = await response.json();
        this.translations[language] = translations;
        return translations;
      } else {
        // Fallback to default language file
        return await this.loadDefaultTranslations(language);
      }
    } catch (error) {
      console.error('Failed to load translations:', error);
      // Fallback to default language file
      return await this.loadDefaultTranslations(language);
    }
  }

  /**
   * Load default translations (fallback)
   */
  async loadDefaultTranslations(lang) {
    try {
      // Try to load from public/lang directory if available
      const response = await fetch(`/lang/${lang}.json`);
      if (response.ok) {
        this.translations[lang] = await response.json();
      }
    } catch (error) {
      console.error('Failed to load default translations:', error);
    }
  }

  /**
   * Set current language and persist preference
   */
  async setLanguage(lang) {
    if (lang === this.currentLanguage) return;
    
    this.currentLanguage = lang;
    this.setStoredLanguage(lang);
    
    // Load translations for new language
    await this.loadTranslations(lang);
    
    // Update document language attribute
    if (typeof document !== 'undefined') {
      document.documentElement.lang = lang;
    }
    
    // Trigger language change event
    if (typeof window !== 'undefined') {
      window.dispatchEvent(new CustomEvent('languageChanged', { detail: { language: lang } }));
    }
    
    // Save preference to backend if user is authenticated
    await this.saveLanguagePreference(lang);
  }

  /**
   * Save language preference to backend
   */
  async saveLanguagePreference(lang) {
    try {
      const token = localStorage.getItem('auth_token');
      if (token) {
        await fetch('/api/v1/language/preference', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          body: JSON.stringify({ language: lang })
        });
      }
    } catch (error) {
      console.error('Failed to save language preference:', error);
    }
  }

  /**
   * Translate a key
   * @param {string} key - Translation key (e.g., 'auth.login')
   * @param {object} params - Parameters to replace in translation
   * @returns {string} Translated text
   */
  t(key, params = {}) {
    const keys = key.split('.');
    
    // First try current language
    let value = this.translations[this.currentLanguage];
    let found = false;
    
    if (value && typeof value === 'object') {
      for (const k of keys) {
        if (value && typeof value === 'object') {
          value = value[k];
          found = true;
        } else {
          found = false;
          break;
        }
      }
    }
    
    // If not found in current language, fallback to English
    if (!found || typeof value !== 'string') {
      if (this.currentLanguage !== 'en' && this.translations['en']) {
        let enValue = this.translations['en'];
        found = false;
        
        for (const enK of keys) {
          if (enValue && typeof enValue === 'object') {
            enValue = enValue[enK];
            found = true;
          } else {
            found = false;
            break;
          }
        }
        
        if (found && typeof enValue === 'string') {
          value = enValue;
        } else {
          // Return key if not found in English either
          return key;
        }
      } else {
        // Return key if translation not found and no English fallback
        return key;
      }
    }
    
    // Replace parameters in translation
    return value.replace(/\{\{(\w+)\}\}/g, (match, paramKey) => {
      return params[paramKey] !== undefined ? params[paramKey] : match;
    });
  }

  /**
   * Get current language
   */
  getLanguage() {
    return this.currentLanguage;
  }

  /**
   * Check if translations are loaded for a language
   */
  hasTranslations(lang = null) {
    const language = lang || this.currentLanguage;
    return this.translations[language] && typeof this.translations[language] === 'object';
  }

  /**
   * Get available languages
   */
  getAvailableLanguages() {
    return [
      { code: 'en', name: 'English', nativeName: 'English' },
      { code: 'bn', name: 'Bangla', nativeName: 'বাংলা' }
    ];
  }
}

// Create singleton instance
const i18n = new I18nService();

export default i18n;
