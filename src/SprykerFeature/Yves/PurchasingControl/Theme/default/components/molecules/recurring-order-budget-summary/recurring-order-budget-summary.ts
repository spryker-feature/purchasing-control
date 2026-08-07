import Component from 'ShopUi/models/component';

enum OptionAttribute {
    costCenterId = 'data-cost-center-id',
    totalAmount = 'data-total-amount',
    usedAmount = 'data-used-amount',
    totalFormatted = 'data-total-formatted',
    usedFormatted = 'data-used-formatted',
    remainingFormatted = 'data-remaining-formatted',
}

export default class RecurringOrderBudgetSummary extends Component {
    protected costCenterSelect: HTMLSelectElement;
    protected budgetSelect: HTMLSelectElement;
    protected totalElement: HTMLElement | null;
    protected usedElement: HTMLElement | null;
    protected remainingElement: HTMLElement | null;
    protected barElement: HTMLElement | null;

    protected init(): void {
        const costCenterSelector = this.getAttribute('cost-center-selector');
        const budgetSelector = this.getAttribute('budget-selector');

        if (!costCenterSelector && !budgetSelector) {
            return;
        }

        if (!costCenterSelector || !budgetSelector) {
            return;
        }

        const costCenterSelect = document.querySelector<HTMLSelectElement>(costCenterSelector);
        const budgetSelect = document.querySelector<HTMLSelectElement>(budgetSelector);

        if (!budgetSelect || !costCenterSelect) {
            return;
        }

        this.costCenterSelect = costCenterSelect;
        this.budgetSelect = budgetSelect;
        this.totalElement = this.querySelector<HTMLElement>(`.${this.jsName}__total`);
        this.usedElement = this.querySelector<HTMLElement>(`.${this.jsName}__used`);
        this.remainingElement = this.querySelector<HTMLElement>(`.${this.jsName}__remaining`);
        this.barElement = this.querySelector<HTMLElement>(`.${this.jsName}__bar`);

        this.filterBudgetOptions();
        this.updateSummary();
        this.mapEvents();
    }

    protected mapEvents(): void {
        this.budgetSelect.addEventListener('change', () => this.updateSummary());
        this.costCenterSelect.addEventListener('change', () => this.onCostCenterChange());
    }

    protected onCostCenterChange(): void {
        this.budgetSelect.value = '';
        this.filterBudgetOptions();
        this.updateSummary();
    }

    protected filterBudgetOptions(): void {
        const selectedCostCenter = this.costCenterSelect.value;

        Array.from(this.budgetSelect.options).forEach((option) => {
            if (!option.value) {
                return;
            }

            option.hidden =
                Boolean(selectedCostCenter) && option.getAttribute(OptionAttribute.costCenterId) !== selectedCostCenter;
        });
    }

    protected updateSummary(): void {
        const option = this.budgetSelect.selectedOptions[0] ?? null;

        this.setText(this.totalElement, option, OptionAttribute.totalFormatted);
        this.setText(this.usedElement, option, OptionAttribute.usedFormatted);
        this.setText(this.remainingElement, option, OptionAttribute.remainingFormatted);
        this.updateBar(option);

        this.classList.toggle(this.getAttribute('hidden-class') ?? 'is-hidden', this.budgetSelect.value === '');
    }

    protected setText(element: HTMLElement | null, option: HTMLOptionElement | null, attribute: OptionAttribute): void {
        if (element) {
            element.textContent = option?.getAttribute(attribute) ?? '';
        }
    }

    protected updateBar(option: HTMLOptionElement | null): void {
        const FULL_BAR_PERCENTAGES = 100;
        if (!this.barElement) {
            return;
        }

        const total = Number(option?.getAttribute(OptionAttribute.totalAmount) ?? 0);
        const used = Number(option?.getAttribute(OptionAttribute.usedAmount) ?? 0);
        const percent =
            total > 0 ? Math.min(FULL_BAR_PERCENTAGES, Math.round((used / total) * FULL_BAR_PERCENTAGES)) : 0;

        this.barElement.style.width = `${percent}%`;
    }
}
