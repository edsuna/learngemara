import { resetArrows, initArrows } from './arrows';
import gematriya from 'gematriya';

export function gemaraCase() {
    return {
        masechtot: [],
        dapim: [],

        tmpSelectedText: '',
        numberOfDapim: 0,
        hasLastAmud: false,
        amudText: false,
        showErrors: false,
        saveErrors: false,
        allowUpdates: true,
        isInit: false,
        saveAs: false,

        theCase: {
            masechet: '',
            daf: '',
            searchText: '',
            gemaraText: '',
            title: '',
            dinType: '',
            caseId: 0,
            inputConditions: {
                consequences: {
                    value: '',
                    notRelevant: false,
                },
                when: {
                    value: '',
                    notRelevant: false,
                },
                where: {
                    value: '',
                    notRelevant: false,
                },
                toWhat: {
                    value: '',
                    notRelevant: false,
                },
                withWhat: {
                    value: '',
                    notRelevant: false,
                },
                how: {
                    value: '',
                    notRelevant: false,
                },
                other: {
                    value: '',
                    notRelevant: false,
                },
                who: {
                    value: '',
                    notRelevant: false,
                },
            },
            act: '',
            public: false,
            _token: '',
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
            amudTextUnavailable: 'The text of this amud could not be loaded. Check your connection and try again.',
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
            saveCase: 'Save',
            saveFailed: 'The case could not be saved. Check your connection and try again.',
            saveCaseAs: 'Save as New',
            caseMasechetError: 'You must select a Masechet',
            caseDafError: 'You must select the Daf',
            caseTextError: 'You must enter the text of the case',
            caseDinTypeError: 'You must select a Din Type',
            caseActError: 'You must fill in the Halachik Act',
            caseConsequencesError: 'You must fill in the Consequences or mark it N/R',
            caseWhenError: 'You must fill in the When or mark it N/R',
            caseWhereError: 'You must fill in the Where or mark it N/R',
            caseToWhatError: 'You must fill in the To What or mark it N/R',
            caseWithWhatError: 'You must fill in the With What or mark it N/R',
            caseHowError: 'You must fill in the How or mark it N/R',
            caseOtherError: 'You must fill in the Other or mark it N/R',
            caseWhoError: 'You must fill in the Who or mark it N/R',
            needLogin: 'Please login to be able to save.',
            public: 'Public',
            text: 'Text',
            actions: 'Actions',
            searchText: 'Text to search for...',
            view: 'View',
            edit: 'Edit',
            remove: 'Remove',
            cancel: 'Cancel',
        },
        hebrewTexts: {
            masechetLabel: 'מסכת',
            selectMasechet: 'בחר מסכת',
            dafLabel: 'דף',
            selectDaf: 'בחר דף',
            amudAlef: '.',
            amudBet: ':',
            showAmudText: 'הצג עמוד טקסט',
            amudTextUnavailable: 'לא ניתן לטעון את טקסט העמוד. בדוק את החיבור ונסה שוב.',
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
            saveCase: 'שמור',
            saveFailed: 'לא ניתן לשמור את המקרה. בדוק את החיבור ונסה שוב.',
            saveCaseAs: 'שמור כחדש',
            caseMasechetError: 'עליך לבחור מסכת',
            caseDafError: 'עליך לבחור את הדף',
            caseTextError: 'עליך להזין את הטקסט של המקרה',
            caseDinTypeError: 'עליך לבחור את סוג הדין',
            caseActError: 'עליך להזין את המעשה ההלכתי',
            caseConsequencesError: 'עליך להזין את התוצאות או לסמן אותו כN/R',
            caseWhenError: 'עליך להזין את המתי או לסמן אותו כN/R',
            caseWhereError: 'עליך להזין את האיפה או לסמן אותו כN/R',
            caseToWhatError: 'עליך להזין את הלמי או למה או לסמן אותו כN/R',
            caseWithWhatError: 'עליך להזין את האם מי או אם מה או לסמן אותו כN/R',
            caseHowError: 'עליך להזין את האיך או לסמן אותו כN/R',
            caseOtherError: 'עליך להזין את העוד או לסמן אותו כN/R',
            caseWhoError: 'עליך להזין את המי או לסמן אותו כN/R',
            needLogin: 'אנא התחבר כדי לשמור',
            public: 'גלוי',
            text: 'טקסט',
            actions: 'פעולות',
            searchText: 'חפש...',
            view: 'לצפייה',
            edit: 'עריכה',
            remove: 'מחק',
            cancel: 'ביטול',
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

        get dafAsText() {
            let d = this.dapim.find(x => x.value == this.theCase.daf);
            return (d !== undefined) ? d.text : '';
        },

        get masechetName() {
            let masechet = this.masechtot.find( x => x.englishName === this.theCase.masechet);
            if (masechet === undefined) {
                return '';
            }
            return (this.selectedLanguage === 'Hebrew') ? masechet.text : masechet.englishName;
        },

        get validationErrors() {
            let errors = this.getValidationErrors();

            return (errors.length) ? errors.join('<br>') : false;
        },

        getMasechtot(theCase, allowUpdates) {
            this.initDapim();
            this.allowUpdates = allowUpdates;

            return fetch('/api/tractates')
            .then(res => res.json())
            .then(masechtot => {
                for (let masechet of masechtot) {
                    this.masechtot.push({
                        englishName: masechet.english_name,
                        text: masechet.name,
                        numberOfDapim: masechet.pages,
                        hasLastAmud: masechet.has_last_amud,
                    });
                }

                if (theCase !== '') {
                    this.initCaseData(theCase);
                }
            });
        },

        initCaseData(theCase) {
            this.isInit = true;
            this.theCase.caseId = theCase.id;
            this.theCase.masechet = theCase.masechet;
            this.theCase.daf = theCase.daf;
            this.theCase.gemaraText = theCase.gemara_text;
            this.theCase.title = theCase.title;
            this.theCase.dinType = theCase.din_type;
            this.theCase.inputConditions.consequences.value = theCase.consequences;
            this.theCase.inputConditions.consequences.notRelevant = theCase.consequences_nr;
            this.theCase.inputConditions.when.value = theCase.when;
            this.theCase.inputConditions.when.notRelevant = theCase.when_nr;
            this.theCase.inputConditions.where.value = theCase.where;
            this.theCase.inputConditions.where.notRelevant = theCase.where_nr;
            this.theCase.inputConditions.toWhat.value = theCase.toWhat;
            this.theCase.inputConditions.toWhat.notRelevant = theCase.toWhat_nr;
            this.theCase.inputConditions.withWhat.value = theCase.withWhat;
            this.theCase.inputConditions.withWhat.notRelevant = theCase.withWhat_nr;
            this.theCase.inputConditions.how.value = theCase.how;
            this.theCase.inputConditions.how.notRelevant = theCase.how_nr;
            this.theCase.inputConditions.other.value = theCase.other;
            this.theCase.inputConditions.other.notRelevant = theCase.other_nr;
            this.theCase.inputConditions.who.value = theCase.who;
            this.theCase.inputConditions.who.notRelevant = theCase.who_nr;
            this.theCase.act = theCase.act;
            this.theCase.public = theCase.public;
            this.selectMasechet();
            setTimeout(initArrows, 250);
            this.isInit = false;
        },

        fillInDapim() {
            this.dapim = [
                {
                    value: '',
                    text: this.localizedTexts.selectDaf,
                }
            ];
            for (let i = 2; i <= this.numberOfDapim; i++) {
                let daf = this.selectedLanguage === 'Hebrew' ? gematriya(i) : i;

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
        },

        selectMasechet() {
            let masechet = this.masechtot.find( x => x.englishName === this.theCase.masechet);
            this.numberOfDapim = masechet.numberOfDapim;
            this.hasLastAmud = masechet.hasLastAmud;

            this.fillInDapim();

            if (!this.isInit) {
                this.theCase.daf = '';
            }
        },

        initDapim() {
            this.numberOfDapim = 176;
            this.hasLastAmud = 1;
            this.fillInDapim();
        },

        getAmudText() {
            let url = "https://www.sefaria.org/api/texts/" + this.theCase.masechet + '.' + this.theCase.daf;
            fetch(url)
            .then(res => {
                if (!res.ok) {
                    throw new Error('Sefaria responded ' + res.status);
                }
                return res.json();
            })
            .then(data => {
                this.amudText = '';
                for (let s of data.he) {
                    this.amudText += ' ' + s;
                }
            })
            .catch(() => {
                // Sefaria is a third party on the open internet, so this will
                // happen. The modal is gated on amudText being truthy, so
                // without a message here the button silently does nothing.
                this.amudText = this.localizedTexts.amudTextUnavailable;
            });
        },

        getMasechetOptionText(masechet) {
            return ((this.selectedLanguage === 'English') ? masechet.englishName.replace('_', ' ') : masechet.text);
        },

        toggleLanguage() {
            this.selectedLanguage = (this.selectedLanguage === 'English') ? 'Hebrew' : 'English';

            // The daf list is built once, at masechet selection, and bakes in
            // the language of that moment -- digits or gematriya. Every other
            // label on the page is reactive, so without this rebuild the daf
            // dropdown alone keeps the language it was created in. The option
            // values are language-independent, so the current selection
            // survives.
            if (this.theCase.masechet) {
                this.fillInDapim();
            }
        },

        resetCase() {
            this.theCase.masechet = '';
            this.resetDaf();
        },

        resetDaf() {
            this.amudText = '';
            this.theCase.daf = '';
            this.resetDafText();
        },

        resetDafText() {
            this.theCase.gemaraText = '';
        },

        resetDinType() {
            this.theCase.dinType = '';
        },

        updateAct() {
            if (this.theCase.act && !this.tmpCase.act) {
                resetArrows();
            }
            else if (!this.theCase.act && this.tmpCase.act) {
                setTimeout(initArrows, 250);
            }
            this.theCase.act = this.tmpCase.act;
        },

        updateCase(piece) {
            this.theCase.inputConditions[piece]['value'] = this.tmpCase[piece];
        },

        resetAct() {
            this.theCase.act = '';
            resetArrows();
        },

        resetCasePiece(piece) {
            this.theCase.inputConditions[piece].value = '';
            this.theCase.inputConditions[piece].notRelevant = false;
        },

        getInputConditionText(condition) {
            if (this.theCase.inputConditions[condition].notRelevant) {
                if (this.tmpCase[condition]) {
                    return this.tmpCase[condition];
                }
                else {
                    return this.localizedTexts.notRelevant;
                }
            }
            else {
                return this.theCase.inputConditions[condition].value;
            }
        },

        getValidationErrors() {
            let errors = [];
            if (!this.theCase.masechet) {
                errors.push(this.localizedTexts.caseMasechetError);
            }
            if (!this.theCase.daf) {
                errors.push(this.localizedTexts.caseDafError);
            }
            if (!this.theCase.gemaraText) {
                errors.push(this.localizedTexts.caseTextError);
            }
            if (!this.theCase.dinType) {
                errors.push(this.localizedTexts.caseDinTypeError);
            }
            if (!this.theCase.act) {
                errors.push(this.localizedTexts.caseActError);
            }
            if (!this.theCase.inputConditions.consequences.value && !this.theCase.inputConditions.consequences.notRelevant) {
                errors.push(this.localizedTexts.caseConsequencesError);
            }
            if (!this.theCase.inputConditions.when.value && !this.theCase.inputConditions.when.notRelevant) {
                errors.push(this.localizedTexts.caseWhenError);
            }
            if (!this.theCase.inputConditions.where.value && !this.theCase.inputConditions.where.notRelevant) {
                errors.push(this.localizedTexts.caseWhereError);
            }
            if (!this.theCase.inputConditions.withWhat.value && !this.theCase.inputConditions.withWhat.notRelevant) {
                errors.push(this.localizedTexts.caseWithWhatError);
            }
            if (!this.theCase.inputConditions.toWhat.value && !this.theCase.inputConditions.toWhat.notRelevant) {
                errors.push(this.localizedTexts.caseToWhatError);
            }
            if (!this.theCase.inputConditions.how.value && !this.theCase.inputConditions.how.notRelevant) {
                errors.push(this.localizedTexts.caseHowError);
            }
            if (!this.theCase.inputConditions.other.value && !this.theCase.inputConditions.other.notRelevant) {
                errors.push(this.localizedTexts.caseOtherError);
            }
            if (!this.theCase.inputConditions.who.value && !this.theCase.inputConditions.who.notRelevant) {
                errors.push(this.localizedTexts.caseWhoError);
            }

            return errors;
        },

        caseComplete() {
            let e = this.getValidationErrors();

            return !e.length;
        },

        submitCase() {
            if (!this.allowUpdates) {
                return false;
            }

            let tokens = document.getElementsByName('_token');
            this.theCase._token = tokens[0].value;
            let url = '/gemara_cases';
            let caseId = this.theCase.caseId;
            let method = 'POST';

            if (this.theCase.caseId) {
                if (!this.saveAs) {
                    url += '/' + this.theCase.caseId;
                    method = 'PUT';
                }
                else {
                    this.theCase.caseId = 0;
                }
            }

            this.theCase._method = method;
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': "application/json",
                },
                body: JSON.stringify(this.theCase)
            })
            .then(async response => {
                if (!response.ok) {
                    throw await response.json().catch(() => ({}));
                }
                return response.json();
            })
            .then(() => {
                this.theCase.caseId = caseId;
                window.location.href = '/gemara_cases/';
            })
            .catch(error => {
                // Restore the id first: "Save as New" zeroes it before the
                // request, so a failed clone must not leave the form thinking
                // it is creating rather than editing.
                this.theCase.caseId = caseId;
                this.saveErrors = this.formatSaveErrors(error);
            });
        },

        /**
         * Turn a failed save into something the user can act on.
         *
         * A 422 carries an `errors` object keyed by field, which is the only
         * place the server says *what* was wrong; the top-level `message` is
         * just a summary. Anything else -- a 500, or a network failure with no
         * body at all -- falls back to a generic line, because reporting
         * nothing is what made this invisible before.
         */
        formatSaveErrors(error) {
            const errors = error && error.errors;

            if (errors && Object.keys(errors).length) {
                return Object.keys(errors)
                    .map(field => (this.localizedTexts['case' + field.charAt(0).toUpperCase() + field.slice(1)] || field)
                        .replace(/<[^>]*>/g, ' ')
                        .trim() + ': ' + [].concat(errors[field]).join(' '))
                    .join('<br>');
            }

            // A server-supplied summary is worth showing; a thrown Error's
            // message is internal wording like "Failed to fetch" and is not.
            if (error && !(error instanceof Error) && error.message) {
                return error.message;
            }

            return this.localizedTexts.saveFailed;
        },

        editGemaraCase(caseId, owner) {
            window.location.href = '/gemara_cases/' + caseId + (owner ? '/edit' : '');
        },
    }
}

if (typeof window !== 'undefined') {
    window.gemaraCase = gemaraCase;
}
