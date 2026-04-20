import { describe, it, expect, beforeEach, vi } from 'vitest';

globalThis.Cookies = { get: vi.fn(() => 'English'), set: vi.fn() };

vi.mock('../arrows', () => ({
    resetArrows: vi.fn(),
    initArrows: vi.fn(),
}));

import { gemaraCase } from '../gemara_case';

describe('gemaraCase', () => {
    let component;

    beforeEach(() => {
        Cookies.get.mockReturnValue('English');
        component = gemaraCase();
    });

    describe('localizedTexts', () => {
        it('returns English texts by default', () => {
            expect(component.localizedTexts.masechetLabel).toBe('Masechet');
        });

        it('returns Hebrew texts when language is Hebrew', () => {
            component.selectedLanguage = 'Hebrew';
            expect(component.localizedTexts.masechetLabel).toBe('מסכת');
        });

        it('falls back to English for unknown language', () => {
            component.selectedLanguage = 'French';
            expect(component.localizedTexts.masechetLabel).toBe('Masechet');
        });
    });

    describe('toggleLanguage', () => {
        it('switches from English to Hebrew', () => {
            component.toggleLanguage();
            expect(component.selectedLanguage).toBe('Hebrew');
        });

        it('switches from Hebrew to English', () => {
            component.selectedLanguage = 'Hebrew';
            component.toggleLanguage();
            expect(component.selectedLanguage).toBe('English');
        });
    });

    describe('dafAsText', () => {
        it('returns empty string when no daf selected', () => {
            expect(component.dafAsText).toBe('');
        });

        it('returns text for matching daf', () => {
            component.dapim = [
                { value: '2a', text: '2a' },
                { value: '2b', text: '2b' },
            ];
            component.theCase.daf = '2a';
            expect(component.dafAsText).toBe('2a');
        });
    });

    describe('masechetName', () => {
        it('returns empty string when no masechet selected', () => {
            expect(component.masechetName).toBe('');
        });

        it('returns English name by default', () => {
            component.masechtot = [{ englishName: 'Berakhot', text: 'ברכות' }];
            component.theCase.masechet = 'Berakhot';
            expect(component.masechetName).toBe('Berakhot');
        });

        it('returns Hebrew name when language is Hebrew', () => {
            component.masechtot = [{ englishName: 'Berakhot', text: 'ברכות' }];
            component.theCase.masechet = 'Berakhot';
            component.selectedLanguage = 'Hebrew';
            expect(component.masechetName).toBe('ברכות');
        });
    });

    describe('getValidationErrors', () => {
        it('returns 13 errors when case is completely empty', () => {
            const errors = component.getValidationErrors();
            expect(errors).toHaveLength(13);
        });

        it('returns no errors when all fields are filled', () => {
            component.theCase.masechet = 'Berakhot';
            component.theCase.daf = '2a';
            component.theCase.gemaraText = 'some gemara text';
            component.theCase.dinType = 'מותר';
            component.theCase.act = 'eating';
            for (const key of Object.keys(component.theCase.inputConditions)) {
                component.theCase.inputConditions[key].value = 'filled';
            }
            expect(component.getValidationErrors()).toHaveLength(0);
        });

        it('accepts notRelevant as alternative to value for input conditions', () => {
            component.theCase.masechet = 'Berakhot';
            component.theCase.daf = '2a';
            component.theCase.gemaraText = 'text';
            component.theCase.dinType = 'מותר';
            component.theCase.act = 'eating';
            for (const key of Object.keys(component.theCase.inputConditions)) {
                component.theCase.inputConditions[key].notRelevant = true;
            }
            expect(component.getValidationErrors()).toHaveLength(0);
        });

        it('includes specific error messages for missing fields', () => {
            const errors = component.getValidationErrors();
            expect(errors).toContain('You must select a Masechet');
            expect(errors).toContain('You must select the Daf');
            expect(errors).toContain('You must enter the text of the case');
            expect(errors).toContain('You must select a Din Type');
            expect(errors).toContain('You must fill in the Halachik Act');
        });

        it('returns Hebrew errors when language is Hebrew', () => {
            component.selectedLanguage = 'Hebrew';
            const errors = component.getValidationErrors();
            expect(errors).toContain('עליך לבחור מסכת');
        });

        it('still requires masechet/daf/text/dinType/act even with notRelevant', () => {
            for (const key of Object.keys(component.theCase.inputConditions)) {
                component.theCase.inputConditions[key].notRelevant = true;
            }
            const errors = component.getValidationErrors();
            expect(errors).toHaveLength(5);
        });
    });

    describe('validationErrors getter', () => {
        it('returns false when case is complete', () => {
            component.theCase.masechet = 'Berakhot';
            component.theCase.daf = '2a';
            component.theCase.gemaraText = 'text';
            component.theCase.dinType = 'מותר';
            component.theCase.act = 'eating';
            for (const key of Object.keys(component.theCase.inputConditions)) {
                component.theCase.inputConditions[key].value = 'filled';
            }
            expect(component.validationErrors).toBe(false);
        });

        it('returns errors joined by <br> when incomplete', () => {
            component.theCase.masechet = 'Berakhot';
            component.theCase.daf = '2a';
            component.theCase.gemaraText = 'text';
            component.theCase.dinType = 'מותר';
            component.theCase.act = 'eating';
            const result = component.validationErrors;
            expect(result).toContain('<br>');
        });
    });

    describe('caseComplete', () => {
        it('returns false when case is empty', () => {
            expect(component.caseComplete()).toBe(false);
        });

        it('returns true when all fields are filled', () => {
            component.theCase.masechet = 'Berakhot';
            component.theCase.daf = '2a';
            component.theCase.gemaraText = 'text';
            component.theCase.dinType = 'מותר';
            component.theCase.act = 'eating';
            for (const key of Object.keys(component.theCase.inputConditions)) {
                component.theCase.inputConditions[key].value = 'filled';
            }
            expect(component.caseComplete()).toBe(true);
        });
    });

    describe('fillInDapim', () => {
        it('generates correct number of dapim with last amud', () => {
            component.numberOfDapim = 4;
            component.hasLastAmud = 1;
            component.fillInDapim();
            // placeholder + (2a,2b) + (3a,3b) + (4a,4b) = 7
            expect(component.dapim).toHaveLength(7);
        });

        it('omits last amud bet when hasLastAmud is 0', () => {
            component.numberOfDapim = 4;
            component.hasLastAmud = 0;
            component.fillInDapim();
            // placeholder + (2a,2b) + (3a,3b) + (4a) = 6
            expect(component.dapim).toHaveLength(6);
        });

        it('starts with placeholder option', () => {
            component.numberOfDapim = 3;
            component.hasLastAmud = 1;
            component.fillInDapim();
            expect(component.dapim[0].value).toBe('');
            expect(component.dapim[0].text).toBe('Select a Daf');
        });

        it('uses English numbers by default', () => {
            component.numberOfDapim = 3;
            component.hasLastAmud = 1;
            component.fillInDapim();
            expect(component.dapim[1].value).toBe('2a');
            expect(component.dapim[1].text).toBe('2a');
        });

        it('uses gematriya for Hebrew daf names', () => {
            component.selectedLanguage = 'Hebrew';
            component.numberOfDapim = 3;
            component.hasLastAmud = 1;
            component.fillInDapim();
            expect(component.dapim[1].text).toContain('ב');
        });

        it('always uses English values regardless of language', () => {
            component.selectedLanguage = 'Hebrew';
            component.numberOfDapim = 3;
            component.hasLastAmud = 1;
            component.fillInDapim();
            expect(component.dapim[1].value).toBe('2a');
        });
    });

    describe('getInputConditionText', () => {
        it('returns value when not marked notRelevant', () => {
            component.theCase.inputConditions.who.value = 'Reuven';
            expect(component.getInputConditionText('who')).toBe('Reuven');
        });

        it('returns localized "Not Relevant" when notRelevant and no tmp', () => {
            component.theCase.inputConditions.who.notRelevant = true;
            component.tmpCase.who = '';
            expect(component.getInputConditionText('who')).toBe('Not Relevant');
        });

        it('returns Hebrew "Not Relevant" when Hebrew', () => {
            component.selectedLanguage = 'Hebrew';
            component.theCase.inputConditions.who.notRelevant = true;
            component.tmpCase.who = '';
            expect(component.getInputConditionText('who')).toBe('לא רלוונטי');
        });

        it('returns tmpCase value when notRelevant and tmp exists', () => {
            component.theCase.inputConditions.who.notRelevant = true;
            component.tmpCase.who = 'draft text';
            expect(component.getInputConditionText('who')).toBe('draft text');
        });

        it('returns empty string when no value and not notRelevant', () => {
            expect(component.getInputConditionText('who')).toBe('');
        });
    });

    describe('getMasechetOptionText', () => {
        it('returns English name with underscores replaced by spaces', () => {
            const m = { englishName: 'Bava_Kamma', text: 'בבא קמא' };
            expect(component.getMasechetOptionText(m)).toBe('Bava Kamma');
        });

        it('returns Hebrew text when language is Hebrew', () => {
            component.selectedLanguage = 'Hebrew';
            const m = { englishName: 'Berakhot', text: 'ברכות' };
            expect(component.getMasechetOptionText(m)).toBe('ברכות');
        });

        it('returns English name without underscores for simple names', () => {
            const m = { englishName: 'Berakhot', text: 'ברכות' };
            expect(component.getMasechetOptionText(m)).toBe('Berakhot');
        });
    });

    describe('reset functions', () => {
        it('resetCase clears masechet and delegates to resetDaf', () => {
            component.theCase.masechet = 'Berakhot';
            component.theCase.daf = '2a';
            component.amudText = 'some text';
            component.resetCase();
            expect(component.theCase.masechet).toBe('');
            expect(component.theCase.daf).toBe('');
            expect(component.amudText).toBe('');
        });

        it('resetDaf clears daf and amudText', () => {
            component.theCase.daf = '2a';
            component.amudText = 'text';
            component.theCase.gemaraText = 'gemara';
            component.resetDaf();
            expect(component.theCase.daf).toBe('');
            expect(component.amudText).toBe('');
            expect(component.theCase.gemaraText).toBe('');
        });

        it('resetDafText clears gemaraText', () => {
            component.theCase.gemaraText = 'text';
            component.resetDafText();
            expect(component.theCase.gemaraText).toBe('');
        });

        it('resetDinType clears dinType', () => {
            component.theCase.dinType = 'מותר';
            component.resetDinType();
            expect(component.theCase.dinType).toBe('');
        });

        it('resetCasePiece clears value and notRelevant', () => {
            component.theCase.inputConditions.who.value = 'Reuven';
            component.theCase.inputConditions.who.notRelevant = true;
            component.resetCasePiece('who');
            expect(component.theCase.inputConditions.who.value).toBe('');
            expect(component.theCase.inputConditions.who.notRelevant).toBe(false);
        });
    });

    describe('updateCase', () => {
        it('copies tmpCase value to inputConditions', () => {
            component.tmpCase.who = 'Shimon';
            component.updateCase('who');
            expect(component.theCase.inputConditions.who.value).toBe('Shimon');
        });
    });

    describe('dinTypes', () => {
        it('contains all 6 din types', () => {
            expect(component.dinTypes).toHaveLength(6);
            expect(component.dinTypes).toContain('מותר');
            expect(component.dinTypes).toContain('אסור');
        });
    });

    describe('initial state', () => {
        it('starts with empty case fields', () => {
            expect(component.theCase.masechet).toBe('');
            expect(component.theCase.daf).toBe('');
            expect(component.theCase.gemaraText).toBe('');
            expect(component.theCase.dinType).toBe('');
            expect(component.theCase.act).toBe('');
            expect(component.theCase.caseId).toBe(0);
        });

        it('starts with all input conditions empty and not notRelevant', () => {
            for (const key of Object.keys(component.theCase.inputConditions)) {
                expect(component.theCase.inputConditions[key].value).toBe('');
                expect(component.theCase.inputConditions[key].notRelevant).toBe(false);
            }
        });

        it('has 8 input conditions', () => {
            expect(Object.keys(component.theCase.inputConditions)).toHaveLength(8);
        });
    });
});
