import Component from 'ShopUi/models/component';

export default class CostCenterSelector extends Component {
    protected budgetRemaining: HTMLElement;
    protected budgetRemainingRow: HTMLElement;
    protected budgetField: HTMLSelectElement;
    protected budgetFieldRow: HTMLElement;
    protected noBudgetsMessage: HTMLElement;
    protected costCenterField: HTMLSelectElement;
    protected sourceBudgetFieldValue: string;
    protected applyWarning: HTMLElement;

    protected init(): void {
        this.budgetField = this.querySelector<HTMLSelectElement>(`.${this.jsName}__budget-control`);
        if (!this.budgetField) {
            return;
        }

        this.budgetRemaining = this.querySelector<HTMLElement>(`.${this.jsName}__budget-remaining`);
        this.budgetRemainingRow = this.querySelector<HTMLElement>(`.${this.jsName}__budget-remaining-row`);
        this.budgetFieldRow = this.querySelector<HTMLElement>(`.${this.jsName}__budget-field-row`);
        this.noBudgetsMessage = this.querySelector<HTMLElement>(`.${this.jsName}__no-budgets`);
        this.applyWarning = this.querySelector<HTMLElement>(`.${this.jsName}__apply-warning`);
        this.costCenterField = this.querySelector<HTMLSelectElement>(`.${this.jsName}__cost-center-control`);
        this.sourceBudgetFieldValue = this.budgetField.value;
        this.mapEvents();
        this.filterBudgetsByCostCenter();
    }

    protected mapEvents(): void {
        this.budgetField.addEventListener('change', this.updateBudgetRemaining.bind(this));
        this.costCenterField?.addEventListener('change', this.filterBudgetsByCostCenter.bind(this));
    }

    /**
     * Budgets belong to a cost center, so only the budgets of the selected one stay selectable.
     */
    protected filterBudgetsByCostCenter(): void {
        if (!this.costCenterField) {
            return;
        }

        const selectedCostCenterId = this.costCenterField.value;
        let isSelectedBudgetFilteredOut = false;
        let hasSelectableBudgets = false;

        Array.from(this.budgetField.options).forEach((option: HTMLOptionElement) => {
            const costCenterId = option.dataset.costCenterId;
            const isMatching = !costCenterId || costCenterId === selectedCostCenterId;

            option.hidden = !isMatching;
            option.disabled = !isMatching;

            if (costCenterId && isMatching) {
                hasSelectableBudgets = true;
            }

            if (!isMatching && option.selected) {
                isSelectedBudgetFilteredOut = true;
            }
        });

        if (isSelectedBudgetFilteredOut) {
            this.budgetField.value = '';
        }

        this.toggleBudgetField(Boolean(selectedCostCenterId) && hasSelectableBudgets);
        this.toggleNoBudgetsMessage(Boolean(selectedCostCenterId) && !hasSelectableBudgets);
        this.updateBudgetRemaining();
    }

    /**
     * The select holds the budgets of every cost center, so a cost center without budgets ends up
     * with an empty looking dropdown unless the whole field gives way to an explanation.
     */
    protected toggleBudgetField(isVisible: boolean): void {
        this.budgetFieldRow?.classList.toggle('is-hidden', !isVisible);
    }

    protected toggleNoBudgetsMessage(isVisible: boolean): void {
        this.noBudgetsMessage?.classList.toggle('is-hidden', !isVisible);
    }

    protected updateBudgetRemaining(): void {
        const remainingAmount = this.budgetField.selectedOptions[0]?.dataset.remainingAmount ?? '';

        if (this.budgetRemaining) {
            this.budgetRemaining.textContent = remainingAmount;
        }

        this.budgetRemainingRow?.classList.toggle('is-hidden', remainingAmount === '');
        this.toggleWarning();
    }

    protected toggleWarning(): void {
        this.applyWarning?.classList.toggle('is-hidden', this.budgetField.value === this.sourceBudgetFieldValue);
    }
}
