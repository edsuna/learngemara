window.languageToggle = () => {
    return {
        englishTexts: {
            LogOut: "Log out",
            LogIn: "Log in",
            Register: "Register",
            buttonText: 'To Hebrew',
            confirmDelete: 'Are you sure?',
        },
        hebrewTexts: {
            LogOut: "יציאה",
            LogIn: "התחבר",
            Register: "הרשמה",
            buttonText: 'לאנגלית',
            confirmDelete: 'האם אתה בטוח?',
        },
        selectedLanguage: Cookies.get('selectedLanguage') === 'undefined' ? 'English' : Cookies.get('selectedLanguage'),

        get localizedTexts() {
            if (this.selectedLanguage === 'English') {
                return this.englishTexts;
            }
            if (this.selectedLanguage === 'Hebrew') {
                return this.hebrewTexts;
            }
            return this.englishTexts;
        },

        toggleLanguage() {
            this.selectedLanguage = (this.selectedLanguage === 'English') ? 'Hebrew' : 'English';
            Cookies.set('selectedLanguage', this.selectedLanguage);
        },

        setTitle(hebrewTitle, englishTitle) {
            this.englishTexts.pageTitle = englishTitle;
            this.hebrewTexts.pageTitle = hebrewTitle;
        },

    }
}
