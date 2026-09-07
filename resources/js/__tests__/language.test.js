import { describe, it, expect, beforeEach, vi } from 'vitest';

globalThis.Cookies = { get: vi.fn(() => 'English'), set: vi.fn() };

import { languageToggle } from '../language';

describe('languageToggle', () => {
    let component;

    beforeEach(() => {
        Cookies.get.mockReturnValue('English');
        Cookies.set.mockClear();
        component = languageToggle();
    });

    describe('localizedTexts', () => {
        // LANG-05
        it('returns English texts by default', () => {
            expect(component.localizedTexts.LogIn).toBe('Log in');
            expect(component.localizedTexts.LogOut).toBe('Log out');
            expect(component.localizedTexts.Register).toBe('Register');
            expect(component.localizedTexts.buttonText).toBe('To Hebrew');
        });

        it('returns Hebrew texts when language is Hebrew', () => {
            component.selectedLanguage = 'Hebrew';
            expect(component.localizedTexts.LogIn).toBe('התחבר');
            expect(component.localizedTexts.LogOut).toBe('יציאה');
            expect(component.localizedTexts.Register).toBe('הרשמה');
            expect(component.localizedTexts.buttonText).toBe('לאנגלית');
        });

        it('falls back to English for unknown language', () => {
            component.selectedLanguage = 'Spanish';
            expect(component.localizedTexts.LogIn).toBe('Log in');
        });
    });

    describe('toggleLanguage', () => {
        // LANG-02
        it('switches from English to Hebrew', () => {
            component.toggleLanguage();
            expect(component.selectedLanguage).toBe('Hebrew');
        });

        // LANG-02

        it('switches from Hebrew to English', () => {
            component.selectedLanguage = 'Hebrew';
            component.toggleLanguage();
            expect(component.selectedLanguage).toBe('English');
        });

        // LANG-03

        it('persists language choice to cookie', () => {
            component.toggleLanguage();
            expect(Cookies.set).toHaveBeenCalledWith('selectedLanguage', 'Hebrew');
        });
    });

    describe('setTitle', () => {
        it('sets page titles for both languages', () => {
            component.setTitle('כותרת', 'Title');
            expect(component.englishTexts.pageTitle).toBe('Title');
            expect(component.hebrewTexts.pageTitle).toBe('כותרת');
        });

        it('is reflected in localizedTexts', () => {
            component.setTitle('דף הבית', 'Home Page');
            expect(component.localizedTexts.pageTitle).toBe('Home Page');
            component.selectedLanguage = 'Hebrew';
            expect(component.localizedTexts.pageTitle).toBe('דף הבית');
        });
    });

    describe('initial state', () => {
        // LANG-01
        it('reads language from cookie', () => {
            expect(Cookies.get).toHaveBeenCalledWith('selectedLanguage');
        });

        // LANG-05

        it('defaults to English when cookie returns undefined', () => {
            Cookies.get.mockReturnValue('undefined');
            const c = languageToggle();
            expect(c.selectedLanguage).toBe('English');
        });
    });
});
