<div wire:ignore.self class="modal fade" id="paymentModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Add Payment</h5>
                <button class="btn-close" data-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3">


                    <div class=" col-md-6" >
                        <label>Type</label>

                        <select id="payment_type"
                                class="form-control ts-static"
                                wire:model="payment_type">
                            <option value="">Select</option>
                            @foreach(\App\Enums\Finance\PaymentType::cases() as $option)
                                <option value="{{ $option->value }}">{{ $option->label() }}</option>
                            @endforeach
                        </select>

                    </div>

                    <div class="col-md-6">
                        <label>Payee</label>
                        <input type="text" wire:model="payee" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label>Amount</label>
                        <input type="number" wire:model="amount" class="form-control">
                    </div>


                    <div class=" col-md-4" >
                        <label>Mode</label>

                        <select id="payment_mode"
                                class="form-control ts-static"
                                wire:model="payment_mode">
                            <option value="">Select</option>
                            @foreach(\App\Enums\Finance\PaymentMode::cases() as $option)
                                <option value="{{ $option->value }}">{{ $option->label() }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-md-4">
                        <label>Date</label>
                        <input type="date" wire:model="payment_date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>Reference</label>
                        <input type="text" wire:model="reference_no" class="form-control">
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
                <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                {{--                    <button wire:click="savePayment" class="btn btn-primary">Save</button>--}}

                <button type="button"
                        class="btn text-white"
                        style="background: {{config('theme.color_accent')}}"
                        wire:click="savePayment"
                        wire:loading.attr="disabled">

                    <span wire:loading.remove wire:target="savePayment">
                        <i class="fas fa-save mr-1"></i> Save Payment
                    </span>

                    <span wire:loading wire:target="savePayment">
                        <span class="spinner-border spinner-border-sm"></span>
                    </span>

                </button>
            </div>

        </div>
    </div>
</div>
