window.gemaraCaseList = () => {
    return {
        englishTexts: {
            masechet: 'Tractate',
            daf: 'Daf',
            title: 'Title',
            dinType: 'Din Type',
            act: 'Act',
            text: 'Text',
            actions: 'Actions',
        },
        hebrewTexts: {
            masechet: 'מסכת',
            daf: 'דף',
            title: 'כותרת',
            dinType: 'סוג הדין',
            act: 'מעשה',
            text: 'טקסט',
            actions: 'פעולות',
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
        },

        editGemaraCase(caseId) {
            window.location.href = '/gemara_cases/' + caseId;
        },
    }
}
