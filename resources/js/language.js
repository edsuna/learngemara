window.languageToggle = () => {
    return {
        englishTexts: {
            LogOut: "Log out",
            LogIn: "Log in",
            Register: "Register",
            buttonText: 'To Hebrew',
        },
        hebrewTexts: {
            LogOut: "יציאה",
            LogIn: "התחבר",
            Register: "הרשמה",
            buttonText: 'לאנגלית',
        },
        selectedLanguage:'English',
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
        },

        setTitle(hebrewTitle, englishTitle) {
            this.englishTexts.pageTitle = englishTitle;
            this.hebrewTexts.pageTitle = hebrewTitle;
        }
    }
}
