<div wire:ignore.self class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label>Payment Type</label>
                        <select wire:model="payment_type" class="form-control">
                            <option value="">-- Select --</option>
                            <option value="ground_rent">Ground Rent</option>
                            <option value="property_rates">Property Rates</option>
                            <option value="compensation">Compensation</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Payee</label>
                        <input type="text" wire:model="payee" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label>Amount</label>
                        <input type="number" step="0.01" wire:model="amount" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label>Payment Mode</label>
                        <input type="text" wire:model="payment_mode" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label>Reference</label>
                        <input type="text" wire:model="reference_no" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Payment Date</label>
                        <input type="date" wire:model="payment_date" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Budget Line</label>
                        <input type="text" wire:model="budget_line" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>
                            <input type="checkbox" wire:model="cet_board_approval">
                            CET Board Approval
                        </label>
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button wire:click="savePayment" class="btn btn-primary">Save</button>
            </div>

        </div>
    </div>
</div>
