import { DEFAULT_SETTINGS, FIELD_LABELS, NUMBERING_EXAMPLES, SECTIONS } from './demo-data.js';

const deepClone = (o) => JSON.parse(JSON.stringify(o));
/** Fields long enough to deserve a textarea instead of a single-line input. */
const LONG_FIELDS = new Set(['address', 'reasons', 'stations', 'receiptFooter']);

export default function settingsApp() {
    return {
        sections: SECTIONS,
        fieldLabels: FIELD_LABELS,
        numberingExamples: NUMBERING_EXAMPLES,

        settings: deepClone(DEFAULT_SETTINGS),
        saved: deepClone(DEFAULT_SETTINGS),
        activeSection: 'general',
        toast: null,
        pendingSection: null,

        init() {
            window.addEventListener('beforeunload', (e) => {
                if (this.isDirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        },
        notify(message, tone = 'info') {
            this.toast = { message, tone };
            clearTimeout(this._toastTimer);
            this._toastTimer = setTimeout(() => (this.toast = null), 2600);
        },

        fieldType(value) {
            if (typeof value === 'boolean') return 'boolean';
            if (typeof value === 'number') return 'number';
            return 'text';
        },
        isLongField(key) {
            return LONG_FIELDS.has(key);
        },
        label(key) {
            return this.fieldLabels[key] || key;
        },

        get sectionDirty() {
            return JSON.stringify(this.settings[this.activeSection]) !== JSON.stringify(this.saved[this.activeSection]);
        },
        get isDirty() {
            return JSON.stringify(this.settings) !== JSON.stringify(this.saved);
        },

        /** Guards navigation between sections so an unsaved edit is never silently discarded. */
        goToSection(key) {
            if (key === this.activeSection) return;
            if (this.sectionDirty) {
                this.pendingSection = key;
                return;
            }
            this.activeSection = key;
        },
        discardAndSwitch() {
            this.settings[this.activeSection] = deepClone(this.saved[this.activeSection]);
            this.activeSection = this.pendingSection;
            this.pendingSection = null;
        },
        saveAndSwitch() {
            this.saveChanges();
            this.activeSection = this.pendingSection;
            this.pendingSection = null;
        },
        cancelSwitch() {
            this.pendingSection = null;
        },

        saveChanges() {
            this.saved[this.activeSection] = deepClone(this.settings[this.activeSection]);
            this.notify(`${this.sections.find((s) => s.key === this.activeSection)?.label} settings saved`, 'success');
        },
        resetSection() {
            this.settings[this.activeSection] = deepClone(this.saved[this.activeSection]);
            this.notify('Changes reverted');
        },
    };
}
