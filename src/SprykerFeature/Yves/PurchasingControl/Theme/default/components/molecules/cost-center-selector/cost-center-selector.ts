import Component from 'ShopUi/models/component';

export default class CostCenterSelector extends Component {
    protected budgetRemaining: HTMLElement;
    protected budgetField: HTMLSelectElement;
    protected sourceBudgetFieldValue: string;
    protected applyWarning: HTMLElement;

    protected readyCallback(): void {}

    protected init(): void {
        this.budgetField = this.querySelector<HTMLSelectElement>(`.${this.jsName}__budget-control`);
        if (!this.budgetField) {
            return;
        }

        this.budgetRemaining = this.querySelector<HTMLElement>(`.${this.jsName}__budget-remaining`);
        this.applyWarning = this.querySelector<HTMLElement>(`.${this.jsName}__apply-warning`);
        this.sourceBudgetFieldValue = this.budgetField.value;
        this.mapEvents();
    }

    protected mapEvents(): void {
        this.budgetField.addEventListener('change', this.updateBudgetRemaining.bind(this));
    }

    protected updateBudgetRemaining(): void {
        const selectedOption = this.budgetField?.selectedOptions[0];
        if (this.budgetRemaining && selectedOption) {
            this.budgetRemaining.textContent = selectedOption.dataset.remainingAmount;
        }

        this.toggleWarning();
    }

    protected toggleWarning(): void {
        this.applyWarning?.classList.toggle('is-hidden', this.budgetField.value === this.sourceBudgetFieldValue);
    }
}
