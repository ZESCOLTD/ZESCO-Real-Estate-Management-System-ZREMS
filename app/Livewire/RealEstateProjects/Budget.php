<?php

namespace App\Livewire\RealEstateProjects;

use App\Models\RealEstateProject;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Budget extends Component
{
    public RealEstateProject $project;

    public array $form = [];

    public const COST_CATEGORIES = [
        'building_civil_architectural' => 'Building / Civil / Architectural Works',
        'electrical_mechanical_plumbing' => 'Electrical / Mechanical / Plumbing Works',
        'external_works' => 'External Works and Site Infrastructure',
        'specialist_works' => 'Other Specialist Works / Equipment',
        'preliminaries' => 'Preliminaries and General Items',
        'professional_fees' => 'Professional / Statutory Fees',
    ];

    public const COST_RISKS = [
        'scope' => 'Scope not fully defined',
        'prices' => 'Price changes / long lead items',
        'access' => 'Site access / occupied premises / logistics',
        'investigations' => 'Additional investigations required',
    ];

    public const SUPPORTING_DOCUMENTS = [
        'BOQ / cost build-up',
        'Quotations',
        'Drawings / scope',
        'Programme',
        'Feasibility assessment',
        'Other',
    ];

    private const SELECT_OPTIONS = [
        'estimate_stage' => ['Preliminary / Concept', 'Feasibility', 'Detailed / BOQ-Based', 'Revised Estimate'],
        'estimate_method' => ['Measured Quantities and Rates', 'Supplier Quotations', 'Benchmark / Similar Works', 'Other'],
        'price_basis' => ['Current Market Rates', 'Recent Contract Rates', 'Budget Quotations', 'Other'],
        'estimate_currency' => ['ZMW', 'Other'],
        'vat_treatment' => ['Excluding VAT', 'Including VAT', 'Not Yet Determined'],
        'boq_attached' => ['Yes', 'No', 'Not applicable'],
        'funding_status' => ['Budget Allocated', 'No Budget Identified'],
    ];

    private const TEXT_FIELDS = [
        'project_title', 'project_reference', 'property_site_name', 'building_structure_name',
        'town_province', 'requesting_department', 'project_description', 'estimate_prepared_by',
        'revision_number', 'estimate_method_other', 'price_basis_other', 'currency_other',
        'scope_reference', 'estimated_duration', 'expected_works_location',
        'major_assumptions', 'excluded_items', 'provisional_sums', 'quotation_validity',
        'financial_year', 'budget_reference', 'budget_information_source', 'other_cost_risk',
        'record_prepared_by', 'prepared_designation_department', 'prepared_signature',
        'reviewed_by', 'reviewed_designation_department', 'review_comments',
        'other_supporting_document',
    ];

    public function mount(RealEstateProject $project): void
    {
        $this->authorize('real_estate_projects.view');
        $this->project = $project;

        $saved = $project->budgetEstimate?->form_data ?? [];
        $this->form = array_replace($this->defaults(), is_array($saved) ? $saved : []);
        $this->form['cost_categories'] = array_replace(
            $this->emptyCostCategories(),
            is_array($this->form['cost_categories'] ?? null) ? $this->form['cost_categories'] : []
        );
        $this->form['cost_risks'] = array_replace(
            $this->emptyCostRisks(),
            is_array($this->form['cost_risks'] ?? null) ? $this->form['cost_risks'] : []
        );
        $this->form['itemized_costs'] = is_array($this->form['itemized_costs'] ?? null)
            ? array_values($this->form['itemized_costs'])
            : $this->emptyItemizedCosts();
        $this->form['supporting_documents'] = is_array($this->form['supporting_documents'] ?? null)
            ? $this->form['supporting_documents']
            : [];
    }

    public function addCostItem(): void
    {
        $this->authorize('real_estate_projects.update');
        if (count($this->form['itemized_costs'] ?? []) < 50) {
            $this->form['itemized_costs'][] = $this->emptyCostItem();
        }
    }

    public function removeCostItem(int $index): void
    {
        $this->authorize('real_estate_projects.update');
        $items = $this->form['itemized_costs'] ?? [];
        if (array_key_exists($index, $items)) {
            unset($items[$index]);
        }
        $this->form['itemized_costs'] = array_values($items) ?: [$this->emptyCostItem()];
    }

    public function save(): void
    {
        $this->authorize('real_estate_projects.update');

        $rules = [];
        foreach (self::TEXT_FIELDS as $field) {
            $rules["form.{$field}"] = ['nullable', 'string', 'max:5000'];
        }
        foreach (['estimate_date', 'price_reference_date', 'prepared_date', 'review_date'] as $field) {
            $rules["form.{$field}"] = ['nullable', 'date_format:Y-m-d'];
        }
        foreach (self::SELECT_OPTIONS as $field => $options) {
            $rules["form.{$field}"] = ['nullable', Rule::in($options)];
        }
        foreach (['contingency', 'taxes', 'amount_allocated', 'funding_gap'] as $field) {
            $rules["form.{$field}"] = ['nullable', 'numeric', 'min:0', 'max:999999999999.99'];
        }
        $rules['form.itemized_costs'] = ['required', 'array', 'min:1', 'max:50'];
        $rules['form.itemized_costs.*'] = ['required', 'array'];
        $rules['form.itemized_costs.*.description'] = ['nullable', 'string', 'max:5000'];
        $rules['form.itemized_costs.*.quantity'] = ['nullable', 'numeric', 'min:0', 'max:1000000'];
        $rules['form.itemized_costs.*.unit'] = ['nullable', 'string', 'max:50'];
        $rules['form.itemized_costs.*.rate'] = ['nullable', 'numeric', 'min:0', 'max:99999999.99'];
        $rules['form.cost_categories'] = ['required', 'array'];
        foreach (self::COST_CATEGORIES as $key => $label) {
            $rules["form.cost_categories.{$key}"] = ['nullable', 'numeric', 'min:0', 'max:999999999999.99'];
        }
        $rules['form.cost_risks'] = ['required', 'array'];
        foreach (self::COST_RISKS as $key => $label) {
            $rules["form.cost_risks.{$key}"] = ['required', 'array'];
            $rules["form.cost_risks.{$key}.status"] = ['nullable', Rule::in(['Yes', 'No', 'Not known'])];
            $rules["form.cost_risks.{$key}.details"] = ['nullable', 'string', 'max:5000'];
        }
        $rules['form.supporting_documents'] = ['array'];
        $rules['form.supporting_documents.*'] = [Rule::in(self::SUPPORTING_DOCUMENTS)];

        $data = $this->validate($rules)['form'];

        foreach (['estimate_method' => 'estimate_method_other', 'price_basis' => 'price_basis_other', 'estimate_currency' => 'currency_other'] as $choice => $other) {
            if (($data[$choice] ?? '') === 'Other' && blank($data[$other] ?? null)) {
                $this->addError("form.{$other}", 'Describe the selected other option.');
            }
        }
        if (in_array('Other', $data['supporting_documents'] ?? [], true) && blank($data['other_supporting_document'] ?? null)) {
            $this->addError('form.other_supporting_document', 'Describe the other supporting document.');
        }
        if (filled($data['prepared_signature'] ?? null) && blank($data['prepared_date'] ?? null)) {
            $this->addError('form.prepared_date', 'Enter the date for the typed signature.');
        }

        $itemizedSubtotal = 0.0;
        $hasItemAmount = false;
        $items = [];
        foreach ($data['itemized_costs'] as $index => $item) {
            $quantity = $item['quantity'] ?? null;
            $rate = $item['rate'] ?? null;
            if (filled($quantity) xor filled($rate)) {
                $this->addError("form.itemized_costs.{$index}.rate", 'Enter both quantity and rate to calculate an amount.');
            }
            $amount = filled($quantity) && filled($rate)
                ? round((float) $quantity * (float) $rate, 2)
                : null;
            if ($amount !== null) {
                $hasItemAmount = true;
                $itemizedSubtotal += $amount;
            }
            $items[] = [
                'description' => $item['description'] ?? '',
                'quantity' => $quantity,
                'unit' => $item['unit'] ?? '',
                'rate' => $rate,
                'amount' => $amount === null ? null : number_format($amount, 2, '.', ''),
            ];
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $categories = [];
        $subtotal = 0.0;
        $hasSummaryAmount = false;
        foreach (self::COST_CATEGORIES as $key => $label) {
            $value = $data['cost_categories'][$key] ?? null;
            $categories[$key] = $value;
            if (filled($value)) {
                $hasSummaryAmount = true;
                $subtotal += (float) $value;
            }
        }
        $contingency = (float) ($data['contingency'] ?? 0);
        $taxes = (float) ($data['taxes'] ?? 0);
        $hasTotal = $hasSummaryAmount || filled($data['contingency'] ?? null) || filled($data['taxes'] ?? null);

        $data['itemized_costs'] = $items;
        $data['cost_categories'] = $categories;
        $data['cost_risks'] = array_map(
            fn ($key) => [
                'status' => $data['cost_risks'][$key]['status'] ?? '',
                'details' => $data['cost_risks'][$key]['details'] ?? '',
            ],
            array_keys(self::COST_RISKS)
        );
        $data['cost_risks'] = array_combine(array_keys(self::COST_RISKS), $data['cost_risks']);
        $data['supporting_documents'] = array_values(array_unique($data['supporting_documents'] ?? []));
        if (! in_array('Other', $data['supporting_documents'], true)) {
            $data['other_supporting_document'] = null;
        }

        $estimate = $this->project->budgetEstimate()->firstOrNew();
        $estimate->fill([
            'form_data' => $data,
            'itemized_subtotal' => $hasItemAmount ? number_format($itemizedSubtotal, 2, '.', '') : null,
            'subtotal_before_contingency_tax' => $hasSummaryAmount ? number_format($subtotal, 2, '.', '') : null,
            'total_estimated_cost' => $hasTotal ? number_format($subtotal + $contingency + $taxes, 2, '.', '') : null,
            'updated_by' => auth()->id(),
        ]);
        if (! $estimate->exists) {
            $estimate->created_by = auth()->id();
        }
        $estimate->save();
        $this->form = $data;

        session()->flash('budget_success', 'Project budget estimate saved.');
    }

    public function render()
    {
        $itemAmounts = [];
        $itemizedSubtotal = 0.0;
        $hasItemAmount = false;
        foreach ($this->form['itemized_costs'] ?? [] as $index => $item) {
            $quantity = $item['quantity'] ?? null;
            $rate = $item['rate'] ?? null;
            $itemAmounts[$index] = filled($quantity) && filled($rate) && is_numeric($quantity) && is_numeric($rate)
                ? round((float) $quantity * (float) $rate, 2)
                : null;
            if ($itemAmounts[$index] !== null) {
                $hasItemAmount = true;
                $itemizedSubtotal += $itemAmounts[$index];
            }
        }

        $summarySubtotal = 0.0;
        $hasSummaryAmount = false;
        foreach (array_keys(self::COST_CATEGORIES) as $key) {
            $value = $this->form['cost_categories'][$key] ?? null;
            if (filled($value) && is_numeric($value)) {
                $hasSummaryAmount = true;
                $summarySubtotal += (float) $value;
            }
        }
        $total = $summarySubtotal
            + (is_numeric($this->form['contingency'] ?? null) ? (float) $this->form['contingency'] : 0)
            + (is_numeric($this->form['taxes'] ?? null) ? (float) $this->form['taxes'] : 0);
        $hasTotal = $hasSummaryAmount || filled($this->form['contingency'] ?? null) || filled($this->form['taxes'] ?? null);

        return view('livewire.real-estate-projects.budget', compact(
            'itemAmounts', 'itemizedSubtotal', 'hasItemAmount',
            'summarySubtotal', 'hasSummaryAmount', 'total', 'hasTotal'
        ));
    }

    private function defaults(): array
    {
        $proposal = $this->project->proposal_data ?? [];
        $user = auth()->user();

        return [
            'project_title' => $this->project->project_title ?? '',
            'project_reference' => $this->project->reference_no ?? '',
            'property_site_name' => $this->project->property_name ?? '',
            'building_structure_name' => $proposal['building_structure_name'] ?? '',
            'town_province' => $this->project->town_location ?? '',
            'requesting_department' => $proposal['originating_department'] ?? '',
            'project_description' => $proposal['intervention_description'] ?? '',
            'estimate_prepared_by' => $user?->name ?? '',
            'estimate_date' => today()->toDateString(),
            'revision_number' => '1',
            'estimate_stage' => '',
            'estimate_method' => '',
            'estimate_method_other' => '',
            'price_basis' => '',
            'price_basis_other' => '',
            'estimate_currency' => 'ZMW',
            'currency_other' => '',
            'vat_treatment' => '',
            'price_reference_date' => '',
            'scope_reference' => '',
            'estimated_duration' => $proposal['desired_timeframe'] ?? '',
            'expected_works_location' => $this->project->town_location ?? '',
            'itemized_costs' => $this->emptyItemizedCosts(),
            'boq_attached' => '',
            'cost_categories' => $this->emptyCostCategories(),
            'contingency' => '',
            'taxes' => '',
            'major_assumptions' => '',
            'excluded_items' => '',
            'provisional_sums' => '',
            'quotation_validity' => '',
            'funding_status' => '',
            'financial_year' => '',
            'budget_reference' => '',
            'amount_allocated' => '',
            'funding_gap' => '',
            'budget_information_source' => '',
            'cost_risks' => $this->emptyCostRisks(),
            'other_cost_risk' => '',
            'record_prepared_by' => $user?->name ?? '',
            'prepared_designation_department' => '',
            'prepared_signature' => '',
            'prepared_date' => '',
            'reviewed_by' => '',
            'reviewed_designation_department' => '',
            'review_comments' => '',
            'review_date' => '',
            'supporting_documents' => [],
            'other_supporting_document' => '',
        ];
    }

    private function emptyCostItem(): array
    {
        return ['description' => '', 'quantity' => '', 'unit' => '', 'rate' => '', 'amount' => null];
    }

    private function emptyItemizedCosts(): array
    {
        return array_fill(0, 8, $this->emptyCostItem());
    }

    private function emptyCostCategories(): array
    {
        return array_fill_keys(array_keys(self::COST_CATEGORIES), '');
    }

    private function emptyCostRisks(): array
    {
        return array_fill_keys(array_keys(self::COST_RISKS), ['status' => '', 'details' => '']);
    }
}
