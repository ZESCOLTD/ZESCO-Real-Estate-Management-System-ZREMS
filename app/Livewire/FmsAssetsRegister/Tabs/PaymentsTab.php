<?php

namespace App\Livewire\FmsAssetsRegister\Tabs;

use App\Models\FmsAssetRegister;
use Livewire\Component;

class PaymentsTab extends Component
{
    public $assetId;
    public $payments = [];

    // Form fields
    public $payment_type;
    public $payee;
    public $amount;
    public $payment_mode;
    public $reference_no;
    public $payment_date;
    public $budget_line;
    public $cet_board_approval = false;

    protected $rules = [
        'payment_type' => 'required|string',
        'amount' => 'required|numeric|min:0',
        'payee' => 'nullable|string',
        'payment_mode' => 'nullable|string',
        'reference_no' => 'nullable|string',
        'payment_date' => 'nullable|date',
        'budget_line' => 'nullable|string',
        'cet_board_approval' => 'boolean',
    ];

    public function mount($assetId)
    {
        $this->assetId = $assetId;

        $this->loadPayments();
    }

    public function loadPayments()
    {
        $asset = FmsAssetRegister::find($this->assetId);

        if (!$asset) {
            $this->payments = collect();
            return;
        }

        $this->payments = $asset->assetPayments()
            ->latest()
            ->get();
    }

    public function savePayment()
    {
        $this->validate();

        $asset = FmsAssetRegister::findOrFail($this->assetId);

        $asset->assetPayments()->create([
            'payment_type' => $this->payment_type,
            'payee' => $this->payee,
            'amount' => $this->amount,
            'payment_mode' => $this->payment_mode,
            'reference_no' => $this->reference_no,
            'payment_date' => $this->payment_date,
            'budget_line' => $this->budget_line,
            'cet_board_approval' => $this->cet_board_approval,
        ]);

        $this->reset([
            'payment_type',
            'payee',
            'amount',
            'payment_mode',
            'reference_no',
            'payment_date',
            'budget_line',
            'cet_board_approval'
        ]);

        $this->loadPayments();

        $this->dispatch('close-modal');
    }

    public function render()
    {
        return view('livewire.fms-assets-register.tabs.payments-tab');
    }
}
