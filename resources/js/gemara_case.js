const { resetArrows, initArrows } = require("./arrows");

window.gemaraCase = () => {
    return {
        masechtot: [],
        dapim: [],

        selectedMasechet: '',
        selectedDaf: '',
        selectedText: '',
        tmpSelectedText: '',
        numberOfDapim: 0,
        hasLastAmud: false,
        amudText: false,
        caseTitle: '',
        dinType: '',

        gCase: {
            consequences: '',
            when: '',
            where: '',
            toWhat: '',
            withWhat: '',
            how: '',
            other: '',
            act: '',
            who: '',
        },
        tmpCase: {
            consequences: '',
            when: '',
            where: '',
            toWhat: '',
            withWhat: '',
            how: '',
            other: '',
            act: '',
            who: '',
        },
        notRelevant: {
            consequences: false,
            when: false,
            where: false,
            toWhat: false,
            withWhat: false,
            how: false,
            other: false,
            act: false,
            who: false,
        },
        when: '',

        dinTypes: [
            'מותר',
            'אסור',
            'חייב',
            'פטור',
            'כשר',
            'פסול',
        ],
        englishTexts: {
            masechetLabel: 'Masechet',
            selectMasechet: 'Select a Masechet',
            dafLabel: 'Daf',
            selectDaf: 'Select a Daf',
            amudAlef: 'a',
            amudBet: 'b',
            showAmudText: 'Show Amud Text',
            titleLabel: 'Case Title',
            caseWho: 'Who',
            caseHow: 'How',
            caseWithWhat: 'With Whom /<p></p>With What',
            caseToWhat: 'To Whom /<p></p>To What',
            caseWhere: 'Where',
            caseWhen: 'When',
            caseConsequences: 'Consequences',
            caseOther: 'Other',
            caseAct: 'Act',
            caseDin: 'Din Type',
            notRelevant: "Not Relevant",
            selectDinType: 'Select Din Type',
        },
        hebrewTexts: {
            masechetLabel: 'מסכת',
            selectMasechet: 'בחר מסכת',
            dafLabel: 'דף',
            selectDaf: 'בחר דף',
            amudAlef: '.',
            amudBet: ':',
            showAmudText: 'הצג עמוד טקסט',
            titleLabel: 'כותרת המקרה',
            caseWho: 'מי',
            caseHow: 'איך',
            caseWithWhat: 'עם מי / עם מה',
            caseToWhat: 'למי / למה',
            caseWhere: 'איפה',
            caseWhen: 'מתי',
            caseConsequences: 'תוצאות',
            caseOther: 'עוד',
            caseAct: 'מעשה',
            caseDin: 'דין',
            notRelevant: 'לא רלוונטי',
            selectDinType: 'בחר סוג הדין',
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

        get dafAsText() {
            let d = this.dapim.find(x => x.value == this.selectedDaf);
            return (d !== undefined) ? d.text : '';
        },

        get masechetName() {
            let masechet = this.masechtot.find( x => x.englishName === this.selectedMasechet);
            if (masechet === undefined) {
                return '';
            }
            return (this.selectedLanguage === 'Hebrew') ? masechet.text : masechet.englishName;
        },

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
            this.resetCase();
        },

        resetCase() {
            this.selectedMasechet = '';
            this.resetDaf();
        },

        resetDaf() {
            this.amudText = '';
            this.selectedDaf = '';
            this.resetDafText();
        },

        resetDafText() {
            this.selectedText = '';
        },

        updateCase(piece) {
            if (piece === 'act') {
                if (this.gCase[piece] && !this.mpCase[piece]) {
                    resetArrows();
                }
                else if (!this.gCase[piece] && this.tmpCase[piece]) {
                    setInterval(initArrows, 250);
                }
            }
            this.gCase[piece] = this.tmpCase[piece];
        },

        resetCasePiece(piece) {
            if (piece === 'act') {
                resetArrows();
            }
            this.gCase[piece] = '';
            this.notRelevant[piece] = false;
        }
    }
}

