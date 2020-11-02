window.gemaraCase = () => {
    return {
        masechtot: [],
        dapim: [],

        selectedMasechet: '',
        selectedDaf: '',
        selectedText: '',
        numberOfDapim: 0,
        hasLastAmud: false,
        amudText: false,

        englishTexts: {
            masechetLabel: 'Masechet',
            selectMasechet: 'Select a Masechet',
            dafLabel: 'Daf',
            selectDaf: 'Select a Daf',
            amudAlef: 'a',
            amudBet: 'b',
            showAmudText: 'Show Amud Text',
        },
        hebrewTexts: {
            masechetLabel: 'מסכת',
            selectMasechet: 'בחר מסכת',
            dafLabel: 'דף',
            selectDaf: 'בחר דף',
            amudAlef: '.',
            amudBet: ':',
            showAmudText: 'הצג עמוד טקסט'
        },
        get localizedTexts() {
            if (this.selectedLanguage === 'English') {
                return this.englishTexts;
            }
            if (this.selectedLanguage === 'Hebrew') {
                return this.hebrewTexts;
            }
            return this.englishTexts;
        },
        selectedLanguage:'English',

        getMasechtot() {
            fetch('/api/tractates')
            .then(res => res.json())
            .then(masechtot => {
                for (let masechet of masechtot) {
                    this.masechtot.push({
                        englishName: masechet.english_name,
                        text: masechet.name,
                        numberOfDapim: masechet.pages,
                        hasLastAmud: masechet.has_last_amud,
                    })
                }
            })
        },

        selectMasechet() {
            this.selected = true;
            let masechet = this.masechtot.find( x => x.englishName === this.selectedMasechet);
            this.numberOfDapim = masechet.numberOfDapim;
            this.hasLastAmud = masechet.hasLastAmud;
            this.dapim = [
                {
                    value: '',
                    text: this.localizedTexts.selectDaf,
                }
            ];
            for (let i = 2; i <= this.numberOfDapim; i++) {
                let daf = this.selectedLanguage === 'Hebrew' ? gematriya().gematriya(i) : i;

                this.dapim.push({
                    value: i + this.englishTexts.amudAlef,
                    text: daf + this.localizedTexts.amudAlef,
                });
                if ((i < this.numberOfDapim) || (this.hasLastAmud === 1)) {
                    this.dapim.push({
                        value: i + this.englishTexts.amudBet,
                        text: daf + this.localizedTexts.amudBet,
                    });
                }
            }

            this.selectedDaf = '';
        },

        getAmudText() {
            let url = "https://www.sefaria.org/api/texts/" + this.selectedMasechet + '.' + this.selectedDaf;
            fetch(url)
            .then(res => res.json())
            .then(data => {
                this.amudText = '';
                for (let s of data.he) {
                    this.amudText += ' ' + s;
                }
            });
        },

        getMasechetOptionText(masechet) {
            return ((this.selectedLanguage === 'English') ? masechet.englishName.replace('_', ' ') : masechet.text);
        },

        toggleLanguage() {
            this.selectedLanguage = (this.selectedLanguage === 'English') ? 'Hebrew' : 'English';
            this.amudText = '';
            this.selectedDaf = '';
            this.selectedMasechet = '';
            this.selectedText = '';
        }
    }
}

