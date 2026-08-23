import { DEFAULT_SETTINGS, FIELD_LABELS, NUMBERING_EXAMPLES, SECTIONS } from './demo-data.js';

const deepClone = (o) => JSON.parse(JSON.stringify(o));
/** Fields long enough to deserve a textarea instead of a single-line input. */
const LONG_FIELDS = new Set(['address', 'reasons', 'stations', 'receiptFooter']);
const boot = window.settingsModule || {};
const routes = window.settingsRoutes || {};

function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function csrfHeaders() {
    const token = csrf();
    if (!token) throw new Error('Security token missing. Refresh the page and try again.');

    return { Accept: 'application/json', 'X-CSRF-TOKEN': token };
}

export default function settingsApp() {
    return {
        sections: boot.sections || SECTIONS,
        fieldLabels: boot.fieldLabels || FIELD_LABELS,
        numberingExamples: boot.numberingExamples || NUMBERING_EXAMPLES,

        settings: deepClone(boot.settings || DEFAULT_SETTINGS),
        saved: deepClone(boot.settings || DEFAULT_SETTINGS),
        activeSection: 'general',
        toast: null,
        pendingSection: null,
        saving: false,

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
        async saveAndSwitch() {
            const saved = await this.saveChanges();
            if (saved) this.activeSection = this.pendingSection;
            this.pendingSection = null;
        },
        cancelSwitch() {
            this.pendingSection = null;
        },

        async saveChanges() {
            if (!routes.section) return false;
            this.saving = true;
            try {
                const response = await fetch(`${routes.section}/${this.activeSection}`, {
                    method: 'PUT',
                    headers: {
                        ...csrfHeaders(),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ values: this.settings[this.activeSection] }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Unable to save settings');
                this.settings = deepClone(data.settings || this.settings);
                this.saved = deepClone(data.settings || this.settings);
                this.notify(`${this.sections.find((s) => s.key === this.activeSection)?.label} settings saved`, 'success');
                return true;
            } catch (error) {
                this.notify(error.message || 'Unable to save settings', 'error');
                return false;
            } finally {
                this.saving = false;
            }
        },
        async resetSection() {
            if (this.sectionDirty || !routes.section) {
                this.settings[this.activeSection] = deepClone(this.saved[this.activeSection]);
                this.notify('Changes reverted');
                return;
            }

            this.saving = true;
            try {
                const response = await fetch(`${routes.section}/${this.activeSection}`, {
                    method: 'DELETE',
                    headers: csrfHeaders(),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Unable to reset settings');
                this.settings = deepClone(data.settings || this.settings);
                this.saved = deepClone(data.settings || this.settings);
                this.notify('Section reset to defaults', 'success');
            } catch (error) {
                this.notify(error.message || 'Unable to reset settings', 'error');
            } finally {
                this.saving = false;
            }
        },
    };
}
