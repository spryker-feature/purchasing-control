import Component from 'ShopUi/models/component';

export default class CostCenterBudgetFilter extends Component {
    protected costCenterSelect: HTMLSelectElement;
    protected budgetSelect: HTMLSelectElement;

    protected readyCallback() {}

    protected init(): void {
        this.costCenterSelect = document.querySelector<HTMLSelectElement>(this.costCenterSelector);
        this.budgetSelect = document.querySelector<HTMLSelectElement>(this.budgetSelector);

        if (!this.costCenterSelect || !this.budgetSelect) {
            return;
        }

        this.filterOptions();
        this.mapEvents();
    }

    protected mapEvents(): void {
        this.costCenterSelect.addEventListener('change', () => this.onCostCenterChange());
    }

    protected onCostCenterChange(): void {
        this.budgetSelect.value = '';
        this.filterOptions();
    }

    protected filterOptions(): void {
        const selectedValue = this.costCenterSelect.value;

        Array.from(this.budgetSelect.options).forEach((option) => {
            if (!option.value) {
                return;
            }

            if (!selectedValue) {
                option.hidden = false;

                return;
            }

            option.hidden = option.getAttribute('data-cost-center-id') !== selectedValue;
        });
    }

    protected get costCenterSelector(): string {
        return this.getAttribute('cost-center-selector');
    }

    protected get budgetSelector(): string {
        return this.getAttribute('budget-selector');
    }
}
